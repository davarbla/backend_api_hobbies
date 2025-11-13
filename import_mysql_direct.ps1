# Simple MySQL import using ODBC
$csvPath = "c:\Workspace\hobbies\AllSource_Code_HobbiesApp_v102\backend_api_hobbies\root\mysql\scripts\tb_category_PROD.csv"
$dsn = "hobbies"

# Check if ODBC driver is available
$drivers = Get-OdbcDriver -ErrorAction SilentlyContinue | Where-Object { $_.Name -like "*MySQL*" }
if (-not $drivers) {
    Write-Host "MySQL ODBC driver not found. Please install MySQL Connector/ODBC." -ForegroundColor Red
    exit 1
}

# Create connection string
$connectionString = "DSN=$dsn;UID=root;PWD=;"

try {
    # Create connection
    $connection = New-Object System.Data.Odbc.OdbcConnection($connectionString)
    $connection.Open()
    Write-Host "Connected to MySQL database via ODBC" -ForegroundColor Green
    
    # Create command
    $command = New-Object System.Data.Odbc.OdbcCommand
    $command.Connection = $connection
    
    # Truncate table
    $command.CommandText = "TRUNCATE TABLE `tb_category`"
    $command.ExecuteNonQuery()
    Write-Host "Table truncated" -ForegroundColor Yellow
    
    # Import data
    $sql = @"
    LOAD DATA LOCAL INFILE '$csvPath'
    INTO TABLE `tb_category`
    FIELDS TERMINATED BY ';'
    ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 LINES
    (id_category, title, description, image, total_interest, total_post, total_like, total_trivia, flag, status, date_created, date_updated, @skip, private, `group`, latitude, location, id_owner, fun, subscribe_fcm, lat, lng, country)
"@
    
    $command.CommandText = $sql
    $rowsAffected = $command.ExecuteNonQuery()
    
    Write-Host "Import completed. $rowsAffected rows affected." -ForegroundColor Green
    
    # Verify
    $command.CommandText = "SELECT COUNT(*) FROM `tb_category`"
    $count = $command.ExecuteScalar()
    Write-Host "Total records in table: $count" -ForegroundColor Cyan
    
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
} finally {
    if ($connection.State -eq 'Open') {
        $connection.Close()
    }
}
