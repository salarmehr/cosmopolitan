#!/usr/bin/env bash
# Run syntax checks and the installed PHPUnit suite in isolated PHP containers.
# Usage: bash scripts/test-php.sh [8.4 8.5 8.6-rc ...]
# Requires Docker and an existing vendor/ directory (composer install).
set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
if [[ ! -f "$project_dir/vendor/bin/phpunit" ]]; then
    echo 'Missing vendor/bin/phpunit. Run composer install first.' >&2
    exit 1
fi
command -v docker >/dev/null
versions=("$@")
if (( ${#versions[@]} == 0 )); then
    versions=(8.4 8.5 8.6-rc)
fi
for version in "${versions[@]}"; do
    if [[ ! "$version" =~ ^[0-9]+\.[0-9]+(\.[0-9]+)?(-rc)?$ ]]; then
        echo "Invalid PHP version: $version" >&2
        exit 1
    fi
done

results_dir="$(mktemp -d "${TMPDIR:-/tmp}/cosmo-php-tests.XXXXXX")"
echo "Logs: $results_dir"
cat > "$results_dir/Dockerfile" <<'DOCKERFILE'
ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev && docker-php-ext-install intl && rm -rf /var/lib/apt/lists/*
RUN if ! php -r 'exit(extension_loaded("mbstring") ? 0 : 1);'; then apt-get update && apt-get install -y --no-install-recommends libonig-dev && docker-php-ext-install -j2 mbstring && rm -rf /var/lib/apt/lists/*; fi
DOCKERFILE

failed=0
for version in "${versions[@]}"; do
    echo "Preparing PHP $version with intl..."
    if ! docker build --build-arg "PHP_VERSION=$version" \
        -t "cosmo-check-php:$version" "$results_dir" \
        > "$results_dir/build-$version.log" 2>&1; then
        cat "$results_dir/build-$version.log"
        failed=1
        continue
    fi
    if docker run --rm --network none \
        -v "$project_dir:/app:ro" -w /app "cosmo-check-php:$version" \
        sh -c '
            php -r '\''echo "PHP ", PHP_VERSION, " / ICU ", INTL_ICU_VERSION, PHP_EOL;'\''
            status=0
            find src tests doc -name "*.php" -print > /tmp/php-files
            while IFS= read -r file; do
                php -d error_reporting=-1 -d display_errors=1 -l "$file" || status=1
            done < /tmp/php-files
            php -d error_reporting=-1 -d display_errors=1 vendor/bin/phpunit --do-not-cache-result --fail-on-risky --fail-on-warning || status=1
            exit "$status"
        ' > "$results_dir/result-$version.log" 2>&1; then
        echo "PHP $version: PASS"
    else
        echo "PHP $version: FAIL"
        failed=1
    fi
    cat "$results_dir/result-$version.log"
done
echo "Logs saved to $results_dir"
exit "$failed"
