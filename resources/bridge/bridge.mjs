import { Agent, createServer, request as httpRequest } from "node:http";
import { readdirSync, readFileSync } from "node:fs";
import { join } from "node:path";

const PORT = Number(process.env.PEST_BRIDGE_PORT);
const LANES = process.env.PEST_BRIDGE_LANES;
const SIGNATURE = process.env.PEST_BRIDGE_SIGNATURE ?? "";
const ROUTE_HEADERS = (process.env.PEST_BRIDGE_ROUTE_HEADERS ?? "")
    .split(",")
    .map((header) => header.trim().toLowerCase())
    .filter((header) => header !== "");
const FORWARD_LANE_HOST = process.env.PEST_BRIDGE_FORWARD_LANE_HOST === "1";
const IDENTITY_PATH = "/__pest-bridge";
const LANE_PATTERN = /-w(\d+)(?=[.:/]|$)/;

const upstreamAgent = new Agent({ keepAlive: false });

const isAlive = (pid) => {
    try {
        process.kill(pid, 0);

        return true;
    } catch (error) {
        return error.code === "EPERM";
    }
};

const liveLanes = () =>
    readdirSync(LANES)
        .filter((name) => /^\d+$/.test(name))
        .map(Number)
        .filter(isAlive);

const hostOf = (value) => {
    try {
        return new URL(value.includes("://") ? value : `http://${value}`).host;
    } catch {
        return null;
    }
};

const laneOf = (req) => {
    const explicit = req.headers["x-pest-lane"];

    if (explicit !== undefined && /^\d+$/.test(String(explicit))) {
        return { lane: Number(explicit), host: null };
    }

    for (const header of ROUTE_HEADERS) {
        const value = req.headers[header];

        if (value === undefined) {
            continue;
        }

        const match = LANE_PATTERN.exec(String(value));

        if (match) {
            return { lane: Number(match[1]), host: hostOf(String(value)) };
        }
    }

    const lanes = liveLanes();

    return lanes.length === 1 ? { lane: lanes[0], host: null } : { lane: null, lanes };
};

const backendOf = (lane) => new URL(readFileSync(join(LANES, String(lane)), "utf8").trim());

const fail = (res, status, message) => {
    res.writeHead(status, { "content-type": "text/plain", "x-pest-bridge-error": "1" });
    res.end(`pest-bridge: ${message}\n`);
};

createServer((req, res) => {
    if (req.url.endsWith(IDENTITY_PATH)) {
        res.writeHead(200, { "content-type": "application/json" });
        res.end(JSON.stringify({ bridge: "pest", port: PORT, pid: process.pid, signature: SIGNATURE }));

        return;
    }

    const routed = laneOf(req);

    if (routed.lane === null) {
        fail(
            res,
            502,
            `cannot tell which Pest process owns ${req.method} ${req.url}: ${routed.lanes.length} processes are running and no header among [x-pest-lane, ${ROUTE_HEADERS.join(", ")}] carries a "-w<lane>" host.`,
        );

        return;
    }

    let backend;

    try {
        backend = backendOf(routed.lane);
    } catch {
        fail(res, 502, `the Pest process ${routed.lane} has no running browser server.`);

        return;
    }

    const headers = { ...req.headers, connection: "close", "x-pest-bridge": "1" };
    headers.host = FORWARD_LANE_HOST && routed.host !== null ? routed.host : backend.host;

    const proxied = httpRequest(
        {
            host: backend.hostname,
            port: backend.port,
            method: req.method,
            path: req.url,
            headers,
            agent: upstreamAgent,
        },
        (upstream) => {
            res.writeHead(upstream.statusCode ?? 502, upstream.headers);
            upstream.pipe(res);
        },
    );

    proxied.on("error", (error) => {
        if (res.headersSent) {
            res.end();

            return;
        }

        fail(res, 502, error.message);
    });

    req.pipe(proxied);
}).listen(PORT, "127.0.0.1", () => {
    console.log(`pest-bridge listening on 127.0.0.1:${PORT}`);
});
