-- Add profile-related columns to the Users table if they don't exist

-- Check if position column exists
IF NOT EXISTS (
    SELECT * FROM sys.columns 
    WHERE object_id = OBJECT_ID(N'[dbo].[Users]') AND name = 'position'
)
BEGIN
    ALTER TABLE [dbo].[Users] ADD [position] NVARCHAR(100) NULL;
END

-- Check if bio column exists
IF NOT EXISTS (
    SELECT * FROM sys.columns 
    WHERE object_id = OBJECT_ID(N'[dbo].[Users]') AND name = 'bio'
)
BEGIN
    ALTER TABLE [dbo].[Users] ADD [bio] NVARCHAR(MAX) NULL;
END

-- Check if profile_picture column exists
IF NOT EXISTS (
    SELECT * FROM sys.columns 
    WHERE object_id = OBJECT_ID(N'[dbo].[Users]') AND name = 'profile_picture'
)
BEGIN
    ALTER TABLE [dbo].[Users] ADD [profile_picture] NVARCHAR(255) NULL;
END

-- Check if last_login column exists
IF NOT EXISTS (
    SELECT * FROM sys.columns 
    WHERE object_id = OBJECT_ID(N'[dbo].[Users]') AND name = 'last_login'
)
BEGIN
    ALTER TABLE [dbo].[Users] ADD [last_login] DATETIME NULL;
END 