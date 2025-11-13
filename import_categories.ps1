# Import MySQL module
Import-Module MySql.Data

# Database connection parameters
$server = "localhost"
$database = "hobbies"
$user = "root"
$password = ""
$port = 3306

# CSV file path
$csvPath = "c:/Workspace/hobbies/AllSource_Code_HobbiesApp_v102/backend_api_hobbies/root/mysql/scripts/tb_category_PROD.csv"

# Create connection string
$connectionString = "server=$server;user id=$user;password=$password;port=$port;database=$database;"

# Create connection object
$connection = New-Object MySql.Data.MySqlClient.MySqlConnection
$connection.ConnectionString = $connectionString

try {
    # Open connection
    $connection.Open()
    Write-Host "Connected to MySQL database" -ForegroundColor Green

    # Create command object
    $command = $connection.CreateCommand()
    
    # Truncate table first
    $command.CommandText = "TRUNCATE TABLE `tb_category`;"
    $rowsAffected = $command.ExecuteNonQuery()
    Write-Host "Truncated tb_category table" -ForegroundColor Yellow

    # Read CSV file
    $csvData = Import-Csv -Path $csvPath -Delimiter ";"
    $totalRows = $csvData.Count
    $importedRows = 0

    # Prepare insert statement
    $insertQuery = @"
    INSERT INTO `tb_category` (
        `id_category`, `title`, `description`, `image`, `total_interest`, `total_post`, 
        `total_like`, `total_trivia`, `flag`, `status`, `date_created`, `date_updated`, 
        `id_category_up`, `private`, `group`, `latitude`, `location`, `id_owner`, 
        `fun`, `subscribe_fcm`, `lat`, `lng`, `country`
    ) VALUES (
        @id_category, @title, @description, @image, @total_interest, @total_post, 
        @total_like, @total_trivia, @flag, @status, @date_created, @date_updated, 
        @id_category_up, @private, @group, @latitude, @location, @id_owner, 
        @fun, @subscribe_fcm, @lat, @lng, @country
    )
"@

    # Process each row
    foreach ($row in $csvData) {
        $command.CommandText = $insertQuery
        
        # Add parameters
        $command.Parameters.Clear()
        $command.Parameters.AddWithValue("@id_category", $row.id_category) | Out-Null
        $command.Parameters.AddWithValue("@title", $row.title) | Out-Null
        $command.Parameters.AddWithValue("@description", $row.description) | Out-Null
        $command.Parameters.AddWithValue("@image", $row.image) | Out-Null
        $command.Parameters.AddWithValue("@total_interest", [int]$row.total_interest) | Out-Null
        $command.Parameters.AddWithValue("@total_post", [int]$row.total_post) | Out-Null
        $command.Parameters.AddWithValue("@total_like", [int]$row.total_like) | Out-Null
        $command.Parameters.AddWithValue("@total_trivia", [int]$row.total_trivia) | Out-Null
        $command.Parameters.AddWithValue("@flag", [int]$row.flag) | Out-Null
        $command.Parameters.AddWithValue("@status", [int]$row.status) | Out-Null
        $command.Parameters.AddWithValue("@date_created", $row.date_created) | Out-Null
        $command.Parameters.AddWithValue("@date_updated", $row.date_updated) | Out-Null
        $command.Parameters.AddWithValue("@id_category_up", [int]$row.id_category_up) | Out-Null
        $command.Parameters.AddWithValue("@private", [int]$row.private) | Out-Null
        $command.Parameters.AddWithValue("@group", [int]$row.group) | Out-Null
        $command.Parameters.AddWithValue("@latitude", $row.latitude) | Out-Null
        $command.Parameters.AddWithValue("@location", $row.location) | Out-Null
        $command.Parameters.AddWithValue("@id_owner", [int]$row.id_owner) | Out-Null
        $command.Parameters.AddWithValue("@fun", [int]$row.fun) | Out-Null
        $command.Parameters.AddWithValue("@subscribe_fcm", $row.subscribe_fcm) | Out-Null
        $command.Parameters.AddWithValue("@lat", [float]$row.lat) | Out-Null
        $command.Parameters.AddWithValue("@lng", [float]$row.lng) | Out-Null
        $command.Parameters.AddWithValue("@country", $row.country) | Out-Null

        try {
            $rowsAffected = $command.ExecuteNonQuery()
            $importedRows++
            Write-Progress -Activity "Importing categories" -Status "$importedRows of $totalRows" -PercentComplete (($importedRows / $totalRows) * 100)
        }
        catch {
            Write-Host "Error inserting row: $_" -ForegroundColor Red
        }
    }

    Write-Host "`nImport completed. Successfully imported $importedRows of $totalRows rows." -ForegroundColor Green
}
catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
finally {
    # Close connection
    if ($connection.State -eq [System.Data.ConnectionState]::Open) {
        $connection.Close()
        Write-Host "Database connection closed" -ForegroundColor Yellow
    }
}

# Keep the window open
Write-Host "`nPress any key to exit..." -ForegroundColor Cyan
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
