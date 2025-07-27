<?php
/**
 * Migration: Create note_shares table
 * This table tracks which notes are shared with which users
 */

class CreateNoteSharesTable {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    public function up() {
        try {
            // Create note_shares table
            $sql = "
            IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[note_shares]') AND type in (N'U'))
            BEGIN
                CREATE TABLE [dbo].[note_shares] (
                    [id] INT IDENTITY(1,1) PRIMARY KEY,
                    [note_id] INT NOT NULL,
                    [shared_with_user_id] INT NOT NULL,
                    [shared_by_user_id] INT NOT NULL,
                    [permission] NVARCHAR(20) DEFAULT 'view', -- 'view' or 'edit'
                    [shared_at] DATETIME DEFAULT GETDATE(),
                    
                    -- Ensure a note can only be shared once with each user
                    CONSTRAINT [UQ_note_shares] UNIQUE([note_id], [shared_with_user_id]),
                    
                    -- Foreign keys
                    CONSTRAINT [FK_note_shares_notes] FOREIGN KEY ([note_id]) 
                        REFERENCES [Notes]([id]) ON DELETE CASCADE,
                    CONSTRAINT [FK_note_shares_shared_with] FOREIGN KEY ([shared_with_user_id]) 
                        REFERENCES [Users]([id]) ON DELETE NO ACTION,
                    CONSTRAINT [FK_note_shares_shared_by] FOREIGN KEY ([shared_by_user_id]) 
                        REFERENCES [Users]([id]) ON DELETE NO ACTION
                )
            END";
            
            $this->db->query($sql);
            echo "Created note_shares table successfully\n";
            
            // Add indexes for performance
            $indexSql = "
            IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_note_shares_user' AND object_id = OBJECT_ID('note_shares'))
            BEGIN
                CREATE INDEX [IX_note_shares_user] ON [note_shares]([shared_with_user_id], [note_id])
            END";
            
            $this->db->query($indexSql);
            echo "Added indexes to note_shares table\n";
            
            return true;
            
        } catch (Exception $e) {
            echo "Error creating note_shares table: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function down() {
        try {
            $sql = "DROP TABLE IF EXISTS [dbo].[note_shares]";
            $this->db->query($sql);
            echo "Dropped note_shares table\n";
            return true;
        } catch (Exception $e) {
            echo "Error dropping note_shares table: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../core/Database.php';
    require_once __DIR__ . '/../../core/EasySQL.php';
    
    $migration = new CreateNoteSharesTable();
    
    if (isset($argv[1]) && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
} 