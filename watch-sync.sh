#!/bin/bash

WATCH_PATH="/Users/mac/Local Sites/piped-peony/app/public/wp-content/themes/hello-theme-child-master"



fswatch -o "$WATCH_PATH" | while read change; do
  echo "🔁 [$(date '+%H:%M:%S')] Change detected. Syncing..."
  ./sync-theme.sh
  echo "✅ [$(date '+%H:%M:%S')] Sync complete."
done
