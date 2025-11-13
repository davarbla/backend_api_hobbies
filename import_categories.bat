@echo off
echo Importing categories to MySQL database...

REM Try to find MySQL installation
if exist "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" (
    set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
) else if exist "C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe" (
    set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe"
) else (
    echo MySQL not found in standard locations. Please ensure MySQL is installed.
    pause
    exit /b 1
)

echo Using MySQL at: %MYSQL_PATH%

REM Run the import
%MYSQL_PATH% -u root -e "USE hobbies; TRUNCATE TABLE tb_category; LOAD DATA LOCAL INFILE 'c:/Workspace/hobbies/AllSource_Code_HobbiesApp_v102/backend_api_hobbies/root/mysql/scripts/tb_category_PROD.csv' INTO TABLE tb_category FIELDS TERMINATED BY ';' ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES;"

if %ERRORLEVEL% EQU 0 (
    echo Import completed successfully!
    
    REM Verify the import
    echo Verifying import...
    %MYSQL_PATH% -u root -e "USE hobbies; SELECT COUNT(*) AS total_categories FROM tb_category;"
) else (
    echo Import failed with error code: %ERRORLEVEL%
)

pause
