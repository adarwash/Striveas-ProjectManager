-- Create Sites table if it doesn't exist
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Sites')
BEGIN
    CREATE TABLE Sites (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(100) NOT NULL,
        location NVARCHAR(200),
        address NVARCHAR(255),
        site_code NVARCHAR(20),
        type NVARCHAR(50),
        status NVARCHAR(20) DEFAULT 'Active',
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE()
    );
    
    PRINT 'Sites table created successfully.';
    
    -- Insert some sample data
    INSERT INTO Sites (name, location, address, site_code, type, status)
    VALUES 
        ('Main Office', 'New York, NY', '123 Main St, New York, NY 10001', 'NYC-HQ', 'Headquarters', 'Active'),
        ('West Coast Office', 'San Francisco, CA', '456 Market St, San Francisco, CA 94105', 'SF-BR', 'Branch', 'Active'),
        ('Chicago Branch', 'Chicago, IL', '789 Michigan Ave, Chicago, IL 60601', 'CHI-BR', 'Branch', 'Active'),
        ('Remote Work Hub', 'Austin, TX', '567 Congress Ave, Austin, TX 78701', 'ATX-HUB', 'Remote Hub', 'Active'),
        ('Client Site - Acme Corp', 'Boston, MA', '321 Tremont St, Boston, MA 02116', 'CLI-ACME', 'Client', 'Temporary');
        
    PRINT 'Sample site data inserted.';
END
ELSE
BEGIN
    PRINT 'Sites table already exists.';
END
GO

-- Create EmployeeSites table if it doesn't exist
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'EmployeeSites')
BEGIN
    CREATE TABLE EmployeeSites (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        site_id INT NOT NULL,
        role NVARCHAR(50) DEFAULT 'Regular Staff',
        assignment_date DATETIME DEFAULT GETDATE(),
        end_date DATETIME NULL,
        is_primary BIT DEFAULT 1,
        notes NVARCHAR(255),
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE(),
        CONSTRAINT FK_EmployeeSites_Users FOREIGN KEY (user_id) REFERENCES Users(id),
        CONSTRAINT FK_EmployeeSites_Sites FOREIGN KEY (site_id) REFERENCES Sites(id)
    );
    
    PRINT 'EmployeeSites table created successfully.';
    
    -- Insert sample data if Users table has records
    IF EXISTS (SELECT TOP 1 * FROM Users)
    BEGIN
        -- Get some user IDs
        DECLARE @userIds TABLE (id INT);
        INSERT INTO @userIds SELECT TOP 5 id FROM Users ORDER BY id;
        
        -- Assign users to sites
        DECLARE @userId INT;
        DECLARE @siteId INT;
        DECLARE @counter INT = 1;
        
        DECLARE user_cursor CURSOR FOR SELECT id FROM @userIds;
        OPEN user_cursor;
        FETCH NEXT FROM user_cursor INTO @userId;
        
        WHILE @@FETCH_STATUS = 0
        BEGIN
            -- Assign to main office
            INSERT INTO EmployeeSites (user_id, site_id, role, is_primary)
            VALUES (@userId, 1, 'Regular Staff', 1);
            
            -- Assign some users to additional sites
            IF @counter % 2 = 0
            BEGIN
                -- Even numbered users get assigned to a second site
                SET @siteId = (@counter % 4) + 2; -- Sites 2-5
                
                INSERT INTO EmployeeSites (user_id, site_id, role, is_primary)
                VALUES (@userId, @siteId, 'Visiting', 0);
            END
            
            SET @counter = @counter + 1;
            FETCH NEXT FROM user_cursor INTO @userId;
        END
        
        CLOSE user_cursor;
        DEALLOCATE user_cursor;
        
        PRINT 'Sample employee-site assignments created.';
    END
END
ELSE
BEGIN
    PRINT 'EmployeeSites table already exists.';
END
GO 