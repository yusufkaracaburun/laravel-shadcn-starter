#!/usr/bin/env bash
set -euo pipefail

#######################
# Configuratie — moet overeenkomen met _setup.sh
#######################
API_DIR="api"
CLIENT_DIR="client"

echo "=== Cleanup: verwijderen van API en CLIENT directories ==="

# Check welke directories bestaan
DIRS_TO_REMOVE=()
if [ -d "$API_DIR" ]; then
  DIRS_TO_REMOVE+=("$API_DIR")
  echo "📁 API directory gevonden: ./$API_DIR"
fi
if [ -d "$CLIENT_DIR" ]; then
  DIRS_TO_REMOVE+=("$CLIENT_DIR")
  echo "📁 CLIENT directory gevonden: ./$CLIENT_DIR"
fi

# Als er niets te verwijderen is
if [ ${#DIRS_TO_REMOVE[@]} -eq 0 ]; then
  echo "✅ Geen directories gevonden om te verwijderen."
  exit 0
fi

# Bevestiging vragen
echo ""
echo "⚠️  Dit zal de volgende directories permanent verwijderen:"
for dir in "${DIRS_TO_REMOVE[@]}"; do
  echo "   - ./$dir"
done
echo ""
read -p "Weet je het zeker? (type 'yes' om door te gaan): " confirm

if [ "$confirm" != "yes" ]; then
  echo "❌ Geannuleerd."
  exit 0
fi

# Verwijder directories
echo ""
for dir in "${DIRS_TO_REMOVE[@]}"; do
  echo "🗑️  Verwijderen van ./$dir..."
  rm -rf "$dir"
  if [ ! -d "$dir" ]; then
    echo "   ✅ ./$dir verwijderd"
  else
    echo "   ❌ Fout bij verwijderen van ./$dir" >&2
    exit 1
  fi
done

echo ""
echo "=== Cleanup voltooid ==="
echo "✅ Alle directories zijn verwijderd."

exit 0

