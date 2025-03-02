-- Create departments table
CREATE TABLE departments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    budget DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NULL
);

-- Add some sample departments
INSERT INTO departments (name, description, budget, created_at)
VALUES 
    ('Engineering', 'Software and hardware engineering department', 1000000.00, GETDATE()),
    ('Marketing', 'Marketing and advertising department', 500000.00, GETDATE()),
    ('Operations', 'Company operations and logistics', 750000.00, GETDATE()),
    ('Research', 'Research and development', 1200000.00, GETDATE());

-- Check if department_id column exists in projects table
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_NAME = 'projects' AND COLUMN_NAME = 'department_id')
BEGIN
    -- Alter projects table to add department_id
    ALTER TABLE projects ADD department_id INT NULL;
    
    -- Add foreign key constraint
    ALTER TABLE projects ADD CONSTRAINT FK_Projects_Departments 
    FOREIGN KEY (department_id) REFERENCES departments(id);
    
    -- Create index on department_id for better performance
    CREATE INDEX IX_Projects_DepartmentId ON projects(department_id);
    
    -- Update existing projects to assign to random departments
    -- This is just to ensure existing data is consistent
    UPDATE projects SET department_id = 1 WHERE department_id IS NULL;
END

-- Check if budget column exists in projects table
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_NAME = 'projects' AND COLUMN_NAME = 'budget')
BEGIN
    -- Alter projects table to add budget
    ALTER TABLE projects ADD budget DECIMAL(15,2) NOT NULL DEFAULT 0;
    
    -- Update existing projects with random budget values between 10000 and 100000
    UPDATE projects SET budget = (ABS(CHECKSUM(NEWID())) % 90000) + 10000;
END 