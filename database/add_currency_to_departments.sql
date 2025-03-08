-- Add currency column to departments table
USE ProjectTracker;
GO

-- Check if the currency column exists
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'departments' AND COLUMN_NAME = 'currency'
)
BEGIN
    BEGIN TRY
        PRINT 'Adding currency column to departments table...';
        
        -- Add the currency column to departments table
        ALTER TABLE departments ADD currency NVARCHAR(3) NOT NULL DEFAULT 'USD';
        
        PRINT 'Currency column added successfully.';
    END TRY
    BEGIN CATCH
        PRINT 'Error adding currency column: ' + ERROR_MESSAGE();
    END CATCH
END
ELSE
BEGIN
    PRINT 'Currency column already exists in departments table.';
END
GO

-- Set default currency for existing records
BEGIN TRY
    PRINT 'Updating existing departments with default currency...';
    
    UPDATE departments 
    SET currency = 'USD' 
    WHERE currency IS NULL OR currency = '';
    
    PRINT 'Departments updated successfully.';
END TRY
BEGIN CATCH
    PRINT 'Error updating departments: ' + ERROR_MESSAGE();
END CATCH
GO

PRINT 'Currency column setup completed.';

-- Show the updated table structure
SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'departments';
GO 