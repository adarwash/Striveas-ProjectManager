-- Add example skills if they don't exist already

-- Programming skills
IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'JavaScript')
    INSERT INTO Skills (name, description, category) VALUES ('JavaScript', 'Web development programming language', 'programming');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'PHP')
    INSERT INTO Skills (name, description, category) VALUES ('PHP', 'Server-side scripting language', 'programming');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Python')
    INSERT INTO Skills (name, description, category) VALUES ('Python', 'General-purpose programming language', 'programming');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'SQL')
    INSERT INTO Skills (name, description, category) VALUES ('SQL', 'Database query language', 'programming');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Java')
    INSERT INTO Skills (name, description, category) VALUES ('Java', 'Object-oriented programming language', 'programming');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'C#')
    INSERT INTO Skills (name, description, category) VALUES ('C#', '.NET programming language', 'programming');

-- Design skills
IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'UI Design')
    INSERT INTO Skills (name, description, category) VALUES ('UI Design', 'User Interface Design', 'design');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'UX Design')
    INSERT INTO Skills (name, description, category) VALUES ('UX Design', 'User Experience Design', 'design');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Graphic Design')
    INSERT INTO Skills (name, description, category) VALUES ('Graphic Design', 'Visual content creation', 'design');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Adobe Photoshop')
    INSERT INTO Skills (name, description, category) VALUES ('Adobe Photoshop', 'Image editing software', 'design');

-- Management skills
IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Project Management')
    INSERT INTO Skills (name, description, category) VALUES ('Project Management', 'Planning and organizing projects', 'management');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Agile')
    INSERT INTO Skills (name, description, category) VALUES ('Agile', 'Agile methodology and practices', 'management');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Scrum')
    INSERT INTO Skills (name, description, category) VALUES ('Scrum', 'Scrum framework for project management', 'management');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Leadership')
    INSERT INTO Skills (name, description, category) VALUES ('Leadership', 'Team leadership skills', 'management');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Communication')
    INSERT INTO Skills (name, description, category) VALUES ('Communication', 'Effective communication skills', 'management');

-- Other skills
IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Problem Solving')
    INSERT INTO Skills (name, description, category) VALUES ('Problem Solving', 'Ability to solve complex problems', 'other');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Time Management')
    INSERT INTO Skills (name, description, category) VALUES ('Time Management', 'Efficiently managing time and priorities', 'other');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Analytical Thinking')
    INSERT INTO Skills (name, description, category) VALUES ('Analytical Thinking', 'Analyzing data and situations', 'other');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Teamwork')
    INSERT INTO Skills (name, description, category) VALUES ('Teamwork', 'Working effectively in a team', 'other');

IF NOT EXISTS (SELECT * FROM Skills WHERE name = 'Attention to Detail')
    INSERT INTO Skills (name, description, category) VALUES ('Attention to Detail', 'Focus on accuracy and details', 'other'); 