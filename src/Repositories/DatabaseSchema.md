```sql
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    category_type VARCHAR(100),
    effective_date DATE,
    expiry_date DATE,
    perpetual VARCHAR(5),
    status VARCHAR(20),
    document_link VARCHAR(255)
);
```