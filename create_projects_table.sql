-- Create Projects table
CREATE TABLE projects (
    id INT PRIMARY KEY IDENTITY(1,1),
    title NVARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    start_date DATE NOT NULL,
    end_date DATE,
    status NVARCHAR(20) NOT NULL DEFAULT 'Active', -- Active, Completed, On Hold, Cancelled
    user_id INT NOT NULL, -- Creator/owner of the project
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create Tasks table
CREATE TABLE tasks (
    id INT PRIMARY KEY IDENTITY(1,1),
    project_id INT NOT NULL,
    title NVARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    status NVARCHAR(20) NOT NULL DEFAULT 'Pending', -- Pending, In Progress, Completed, Testing, Blocked
    priority NVARCHAR(10) NOT NULL DEFAULT 'Medium', -- Low, Medium, High, Critical
    due_date DATE,
    assigned_to INT, -- User assigned to the task
    created_by INT NOT NULL, -- User who created the task
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert sample project data (if user id 1 exists)
INSERT INTO projects (title, description, start_date, end_date, status, user_id)
VALUES 
('Website Redesign', 'Complete overhaul of company website with modern design and improved UX', '2024-01-01', '2024-03-31', 'Completed', 1),
('Database Implementation', 'Design and implement new database structure for the inventory system', '2024-02-15', '2024-05-20', 'Active', 1),
('Mobile App Development', 'Develop a cross-platform mobile application for customer engagement', '2024-03-01', '2024-08-31', 'Active', 1),
('Security Audit', 'Comprehensive security audit of all systems and implementation of recommendations', '2024-04-10', '2024-05-15', 'On Hold', 1);

-- Insert sample tasks for the Website Redesign project
INSERT INTO tasks (project_id, title, description, status, priority, due_date, assigned_to, created_by)
VALUES
(1, 'Design mockups', 'Create wireframes and design mockups for all pages', 'Completed', 'High', '2024-01-15', 1, 1),
(1, 'Frontend development', 'Implement HTML/CSS/JS based on approved designs', 'Completed', 'Medium', '2024-02-10', 1, 1),
(1, 'Backend integration', 'Connect frontend to backend services', 'Completed', 'High', '2024-03-01', 1, 1),
(1, 'Testing and bug fixes', 'Identify and fix any issues before launch', 'Completed', 'Critical', '2024-03-20', 1, 1);

-- Insert sample tasks for the Database Implementation project
INSERT INTO tasks (project_id, title, description, status, priority, due_date, assigned_to, created_by)
VALUES
(2, 'Schema design', 'Design database schema with all tables and relationships', 'Completed', 'High', '2024-02-28', 1, 1),
(2, 'Data migration plan', 'Create plan for migrating data from old system to new database', 'Completed', 'Medium', '2024-03-15', 1, 1),
(2, 'Implementation', 'Set up database and implement schema', 'In Progress', 'High', '2024-04-15', 1, 1),
(2, 'Data migration', 'Execute data migration according to plan', 'Pending', 'Critical', '2024-05-01', 1, 1),
(2, 'Testing', 'Test database functionality and performance', 'Pending', 'High', '2024-05-15', 1, 1);

-- Insert sample tasks for the Mobile App Development project
INSERT INTO tasks (project_id, title, description, status, priority, due_date, assigned_to, created_by)
VALUES
(3, 'Requirements gathering', 'Identify all requirements and features for the app', 'Completed', 'High', '2024-03-15', 1, 1),
(3, 'UI/UX design', 'Design user interface and experience for all app screens', 'In Progress', 'High', '2024-04-15', 1, 1),
(3, 'Front-end development', 'Implement app UI using Flutter framework', 'Pending', 'Medium', '2024-06-15', 1, 1),
(3, 'Back-end API development', 'Create RESTful APIs for the app', 'Pending', 'High', '2024-06-30', 1, 1),
(3, 'Testing and deployment', 'Test on multiple devices and deploy to app stores', 'Pending', 'Critical', '2024-08-15', 1, 1); 