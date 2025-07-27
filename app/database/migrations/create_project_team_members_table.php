<?php
/**
 * Migration: Create project_team_members table
 * This table tracks which users are members of which projects
 */

class CreateProjectTeamMembersTable {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    public function up() {
        try {
            // Create project_team_members table if it doesn't exist
            $sql = "
            IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[project_team_members]') AND type in (N'U'))
            BEGIN
                CREATE TABLE [dbo].[project_team_members] (
                    [id] INT IDENTITY(1,1) PRIMARY KEY,
                    [project_id] INT NOT NULL,
                    [user_id] INT NOT NULL,
                    [role] NVARCHAR(50) DEFAULT 'member', -- 'owner', 'member', 'viewer'
                    [added_at] DATETIME DEFAULT GETDATE(),
                    [added_by] INT NULL,
                    
                    -- Ensure a user can only be added once to each project
                    CONSTRAINT [UQ_project_team_members] UNIQUE([project_id], [user_id]),
                    
                    -- Foreign keys
                    CONSTRAINT [FK_project_team_members_projects] FOREIGN KEY ([project_id]) 
                        REFERENCES [Projects]([id]) ON DELETE CASCADE,
                    CONSTRAINT [FK_project_team_members_users] FOREIGN KEY ([user_id]) 
                        REFERENCES [Users]([id]) ON DELETE CASCADE,
                    CONSTRAINT [FK_project_team_members_added_by] FOREIGN KEY ([added_by]) 
                        REFERENCES [Users]([id]) ON DELETE NO ACTION
                )
            END";
            
            $this->db->query($sql);
            echo "Created/verified project_team_members table\n";
            
            // Add indexes for performance
            $indexSql = "
            IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_project_team_members_user' AND object_id = OBJECT_ID('project_team_members'))
            BEGIN
                CREATE INDEX [IX_project_team_members_user] ON [project_team_members]([user_id], [project_id])
            END";
            
            $this->db->query($indexSql);
            echo "Added indexes to project_team_members table\n";
            
            // Populate table with existing project creators if empty
            $checkSql = "SELECT COUNT(*) as count FROM project_team_members";
            $result = $this->db->select($checkSql);
            
            if ($result && $result[0]['count'] == 0) {
                // Add project creators as owners
                $populateSql = "
                    INSERT INTO project_team_members (project_id, user_id, role, added_at)
                    SELECT id, user_id, 'owner', created_at
                    FROM Projects
                    WHERE user_id IS NOT NULL";
                
                $this->db->query($populateSql);
                echo "Populated project_team_members with project creators\n";
            }
            
            return true;
            
        } catch (Exception $e) {
            echo "Error creating project_team_members table: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function down() {
        try {
            $sql = "DROP TABLE IF EXISTS [dbo].[project_team_members]";
            $this->db->query($sql);
            echo "Dropped project_team_members table\n";
            return true;
        } catch (Exception $e) {
            echo "Error dropping project_team_members table: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../core/Database.php';
    require_once __DIR__ . '/../../core/EasySQL.php';
    
    $migration = new CreateProjectTeamMembersTable();
    
    if (isset($argv[1]) && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
} 