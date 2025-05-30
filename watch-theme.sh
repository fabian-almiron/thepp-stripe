#!/bin/bash

WATCH_PATH="/Users/mac/Local Sites/piped-peony/app/public/wp-content/"

fswatch -o "$WATCH_PATH" | while read change; do
  echo "🔁 Change detected. Syncing to $HOST..."
  ./sync-theme.sh
done

