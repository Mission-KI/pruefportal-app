# Local Monitoring Setup

View CakePHP application logs (error.log, debug.log) in Grafana.

## Quick Start

```bash
# 1. Start the backend (from backend/ directory)
bash start.sh

# 2. Start monitoring stack
cd monitoring && bash start.sh

# 3. Open Grafana
open http://localhost:3001
```

## View Logs in Grafana

### Access Grafana

1. Open http://localhost:3001
2. Login: `admin` / `admin` (or skip - anonymous access enabled)

### View Logs

1. Click **Explore** (compass icon in left sidebar)
2. Ensure **Loki** is selected as datasource (top dropdown)
3. Click **Label browser** button or enter query manually
4. Select labels: `job` = `cakephp`
5. Click **Run query** (or press Shift+Enter)
6. Adjust time range in top-right (e.g., "Last 1 hour")

### Query Examples

```
{job="cakephp"}                    # All CakePHP logs
{job="cakephp", level="error"}     # Only errors
{job="cakephp", level="warning"}   # Only warnings
{job="cakephp"} |= "Exception"     # Logs containing "Exception"
{job="cakephp"} |~ "(?i)database"  # Case-insensitive search for "database"
{job="cakephp"} | json             # Parse JSON logs
```

### Tips

- **Live tail**: Click the "Live" button to stream logs in real-time
- **Filter by text**: Add `|= "search term"` to filter log content
- **Expand logs**: Click on a log line to see full details
- **Time range**: Use the time picker to narrow down when errors occurred

## Generate Test Logs

```bash
# Info log (200 OK) -> debug.log
curl http://localhost:8070/log-test/info

# Warning log (200 OK) -> error.log
curl http://localhost:8070/log-test/warning

# Error log (500 Error) -> error.log
curl http://localhost:8070/log-test/error

# Exception with stack trace (500 Error) -> error.log
curl http://localhost:8070/log-test/exception
```

## Architecture

```
logs/              <- CakePHP writes logs here
  |
  v
[Promtail]         <- Reads log files, parses levels
  |
  v
[Loki]             <- Stores and indexes logs
  |
  v
[Grafana]          <- Query and visualize logs
```
