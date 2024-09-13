log_date=$(date +"%Y-%m-%d")
log_file="storage/logs/laravel-$log_date.log"

# Check if optional parameters are passed
if [ $# -gt 0 ]; then
    # Concatenate optional parameters to the log file path
    log_file+=" $@"
fi

tail -f "$log_file"
