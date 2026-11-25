import { Agent, createServer, request as httpRequest } from "node:http";
import { readdirSync, readFileSync } from "node:fs";
import { join } from "node:path";
import { domainToUnicode } from "node:url";

const PORT = Number(process.env.PEST_BRIDGE_PORT);
const LANES = process.env.PEST_BRIDGE_LANES;
const SIGNATURE = process.env.PEST_BRIDGE_SIGNATURE ?? "";
const ROUTE_HEADERS = (process.env.PEST_BRIDGE_ROUTE_HEADERS ?? "")
    .split(",")
    .map((header) => header.trim().toLowerCase())
    .filter((header) => header !== "");
const FORWARD_LANE_HOST = process.env.PEST_BRIDGE_FORWARD_LANE_HOST === "1";
const IDENTITY_PATH = "/__pest-bridge";
const LANE_PATTERN = /-w(\d+)(?=[.:]|$)/g;

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

const urlOf = (value) => {
    try {
        return new URL(value.includes("://") ? value : `http://${value}`);
    } catch {
        return null;
    }
};

// The lane is read in the Unicode form of the name: an internationalized label is written in ASCII as a whole
// ("café-w4123" becomes "xn--caf-w4123-d4a"), which hides the lane Pest appended to it.
const laneNameOf = (url) => `${domainToUnicode(url.hostname) || url.hostname}${url.port === "" ? "" : `:${url.port}`}`;

// The URL parser drops a default port ("pest-w4123.localhost:80" becomes "pest-w4123.localhost"); the host forwarded
// to the backend keeps the port the header names, or the backend would build its links on its own port.
const hostOf = (value, url) => {
    const authority = (value.includes("://") ? value.slice(value.indexOf("://") + 3) : value).split(/[/?#]/)[0];

    if (url.port !== "" || !/:\d+$/.test(authority)) {
        return url.host;
    }

    return `${url.host}:${url.protocol === "https:" ? 443 : 80}`;
};

const routeHeaderLanes = (req) => {
    const found = [];

    for (const header of ROUTE_HEADERS) {
        const value = req.headers[header];

        if (value === undefined) {
            continue;
        }

        const url = urlOf(String(value));
        const matches = url === null ? [] : [...laneNameOf(url).matchAll(LANE_PATTERN)];

        if (matches.length > 0) {
            found.push({ lane: Number(matches[matches.length - 1][1]), host: hostOf(String(value), url) });
        }
    }

    return found;
};

const laneOf = (req) => {
    const explicit = req.headers["x-pest-lane"];
    const found = routeHeaderLanes(req);

    if (explicit !== undefined && /^\d+$/.test(String(explicit))) {
        const lane = Number(explicit);

        return { lane, host: found.find((entry) => entry.lane === lane)?.host ?? null };
    }

    if (found.length > 0) {
        return found[0];
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
    if (req.url.split("?")[0].endsWith(IDENTITY_PATH)) {
        res.writeHead(200, { "content-type": "application/json" });
        res.end(JSON.stringify({ bridge: "pest", port: PORT, pid: process.pid, signature: SIGNATURE, lanes: LANES }));

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
    delete headers["x-pest-lane-host"];

    if (routed.host !== null) {
        headers["x-pest-lane-host"] = routed.host;
    }
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
