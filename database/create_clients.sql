-- Create Clients table
CREATE TABLE IF NOT EXISTS Clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    industry VARCHAR(100),
    status ENUM('Active', 'Inactive', 'Prospect', 'Former') DEFAULT 'Active',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create SiteClients junction table
CREATE TABLE IF NOT EXISTS SiteClients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    client_id INT NOT NULL,
    relationship_type ENUM('Primary', 'Secondary', 'Prospect', 'Former', 'Partner', 'Vendor', 'Standard') DEFAULT 'Standard',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES Sites(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE,
    UNIQUE KEY (site_id, client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample client data
INSERT INTO Clients (name, contact_person, email, phone, address, industry, status, notes) VALUES
('Acme Corporation', 'John Doe', 'john.doe@acme.com', '555-123-4567', '123 Main St, Business City, 12345', 'Manufacturing', 'Active', 'Our first major client'),
('TechSolutions Inc.', 'Jane Smith', 'jane.smith@techsolutions.com', '555-987-6543', '456 Innovation Ave, Tech Park, 54321', 'Technology', 'Active', 'IT services provider'),
('Global Retail Group', 'Mike Johnson', 'mike.j@globalretail.com', '555-567-8901', '789 Market St, Commerce City, 67890', 'Retail', 'Active', 'Chain store operator'),
('Healthcare Partners', 'Sarah Williams', 'sarah@healthcare-partners.org', '555-234-5678', '321 Medical Dr, Wellness Town, 45678', 'Healthcare', 'Active', 'Regional medical network'),
('Financial Services Ltd', 'David Brown', 'dbrown@financialservices.com', '555-876-5432', '555 Money Ln, Banking District, 89012', 'Finance', 'Active', 'Investment advisory firm');

-- Assign sample clients to sites (assumes you have Site IDs 1, 2, and 3 already)
-- You may need to adjust the site_id values based on your actual site IDs
INSERT INTO SiteClients (site_id, client_id, relationship_type) VALUES
(1, 1, 'Primary'),   -- Acme Corporation at Site 1
(1, 3, 'Secondary'), -- Global Retail Group at Site 1
(2, 2, 'Primary'),   -- TechSolutions at Site 2
(2, 4, 'Partner'),   -- Healthcare Partners at Site 2
(3, 5, 'Primary'),   -- Financial Services at Site 3
(3, 1, 'Secondary'); -- Acme Corporation at Site 3 