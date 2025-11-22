# SplashLeadRouter

**Multi-tenant Real Estate Lead Routing SaaS Platform**

A complete PHP & MySQL SaaS application for real estate agencies to automatically route and manage incoming leads based on zones, rules, and agent performance.

## Features

- **Multi-tenant Architecture**: Each real estate agency operates independently
- **Intelligent Lead Routing**: Automatically assign leads to agents based on:
  - Geographic zones
  - Budget ranges
  - Property types
  - Lead sources
  - Agent capacity and performance
- **Agent Management**: Track agent performance, capacity, and workload
- **Zone-based Territory Management**: Define and manage geographical zones
- **Flexible Routing Rules**: Create custom rules with priorities and strategies
- **Lead Management**: Complete lead lifecycle tracking (new → contacted → qualified → closed)
- **REST API**: External integrations for lead ingestion and status updates
- **Subscription & Billing**: Plans with usage limits and quota enforcement
- **Dashboard & Analytics**: Real-time insights and performance metrics
- **CSV Import**: Bulk import leads from external sources
- **Email Notifications**: Simulated email system for lead assignments

## Requirements

- **PHP**: 7.0 or higher (compatible up to PHP 8.x)
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Apache/Nginx**: with mod_rewrite enabled
- **PHP Extensions**:
  - PDO
  - pdo_mysql
  - json
  - mbstring
  - session

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/SplashLeadRouter.git
cd SplashLeadRouter
```

### 2. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and update your database credentials:

```env
DB_HOST=localhost
DB_NAME=splashleadrouter
DB_USER=root
DB_PASS=your_password
```

### 3. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE splashleadrouter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 4. Import Database Schema

```bash
mysql -u root -p splashleadrouter < database.sql
```

This will create all tables, indexes, foreign keys, and populate demo data.

### 5. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
```

### 6. Configure Web Server

#### Apache

The project includes `.htaccess` files. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configure your virtual host:

```apache
<VirtualHost *:80>
    ServerName splashleadrouter.local
    DocumentRoot /path/to/SplashLeadRouter/public

    <Directory /path/to/SplashLeadRouter/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashleadrouter-error.log
    CustomLog ${APACHE_LOG_DIR}/splashleadrouter-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name splashleadrouter.local;
    root /path/to/SplashLeadRouter/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 7. Access the Application

Open your browser and navigate to:

```
http://splashleadrouter.local
```

Or if using localhost:

```
http://localhost/SplashLeadRouter/public
```

## Demo Credentials

The database comes pre-populated with demo data:

### Platform Admin
- **Email**: admin@splashleadrouter.com
- **Password**: password

### Tenant Admin (Dubai Premium Properties)
- **Email**: ahmed@dubaiproperties.com
- **Password**: password

### Agent (Senior Agent)
- **Email**: sarah@dubaiproperties.com
- **Password**: password

### Tenant Admin (Cairo Elite Estates)
- **Email**: hassan@cairoelite.com
- **Password**: password

## Project Structure

```
SplashLeadRouter/
├── app/
│   ├── controllers/          # All controller classes
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── LeadController.php
│   │   ├── AgentController.php
│   │   ├── ZoneController.php
│   │   ├── RoutingRuleController.php
│   │   └── ApiController.php
│   ├── models/               # All model classes
│   │   ├── User.php
│   │   ├── Tenant.php
│   │   ├── Lead.php
│   │   ├── Zone.php
│   │   ├── RoutingRule.php
│   │   └── ...
│   ├── views/                # All view templates
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── leads/
│   │   ├── agents/
│   │   ├── zones/
│   │   └── routing/
│   ├── core/                 # MVC framework core
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   └── View.php
│   └── helpers/              # Helper classes
│       ├── Validator.php
│       ├── CSRF.php
│       ├── FileUpload.php
│       ├── Email.php
│       └── RoutingEngine.php
├── config/                   # Configuration files
│   ├── app.php
│   └── database.php
├── public/                   # Web root (entry point)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       └── js/
├── storage/                  # Uploads and logs
│   └── uploads/
├── tests/                    # Test files
├── database.sql              # Database schema with demo data
├── .env.example              # Environment configuration example
├── .htaccess                 # Root htaccess
└── README.md                 # This file
```

## REST API Documentation

### Authentication

All API requests require an API key passed via the `X-API-KEY` header:

```bash
X-API-KEY: your_api_key_here
```

API keys are configured per tenant and can be found in the database `api_keys` table.

Demo API Keys:
- Dubai Premium Properties: `dprop_live_4f8a9b2c1d3e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6`
- Cairo Elite Estates: `celite_live_7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6t7u8v9w0x1y2z3a4`

### Endpoints

#### 1. Create Lead

**POST** `/api/leads`

Create a new lead and automatically route to an agent.

**Request Body:**

```json
{
  "name": "John Smith",
  "phone": "+971-55-123-4567",
  "email": "john@example.com",
  "source": "Website Form",
  "campaign": "Summer Sale 2025",
  "property_type": "Apartment",
  "budget_min": 800000,
  "budget_max": 1200000,
  "city": "Dubai",
  "area": "Dubai Marina",
  "notes": "Looking for sea view property"
}
```

**Required Fields:**
- `name` (string)
- `phone` (string)

**Optional Fields:**
- `email` (string)
- `source` (string)
- `campaign` (string)
- `property_type` (string)
- `budget_min` (number)
- `budget_max` (number)
- `city` (string)
- `area` (string)
- `notes` (string)

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Lead created and routed successfully",
  "data": {
    "lead_id": 123,
    "status": "assigned",
    "assigned_agent_id": 5,
    "assignment_status": "assigned",
    "agent_name": "Sarah Johnson",
    "rule_used": "Dubai Marina Zone Rule",
    "routing_reason": "Zone-based assignment: Dubai Marina"
  }
}
```

**cURL Example:**

```bash
curl -X POST http://localhost/api/leads \
  -H "X-API-KEY: dprop_live_4f8a9b2c1d3e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Smith",
    "phone": "+971-55-123-4567",
    "email": "john@example.com",
    "city": "Dubai",
    "area": "Dubai Marina",
    "property_type": "Apartment",
    "budget_min": 800000,
    "budget_max": 1200000
  }'
```

#### 2. Update Lead Status

**PATCH/POST** `/api/leads/{id}`

Update the status of an existing lead.

**Request Body:**

```json
{
  "status": "contacted",
  "notes": "Called client, scheduled viewing for next week"
}
```

**Valid Status Values:**
- `new`
- `assigned`
- `contacted`
- `qualified`
- `unqualified`
- `closed_won`
- `closed_lost`

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Lead updated successfully",
  "data": {
    "lead_id": 123,
    "status": "contacted",
    "assigned_agent_id": 5,
    "updated_at": "2025-01-22 14:30:00"
  }
}
```

**cURL Example:**

```bash
curl -X POST http://localhost/api/leads/123 \
  -H "X-API-KEY: dprop_live_4f8a9b2c1d3e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "contacted",
    "notes": "Client is interested in viewing properties this weekend"
  }'
```

#### 3. Get Lead Details

**GET** `/api/leads/{id}`

Retrieve full details of a specific lead.

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "id": 123,
    "tenant_id": 1,
    "name": "John Smith",
    "phone": "+971-55-123-4567",
    "email": "john@example.com",
    "source": "Website Form",
    "property_type": "Apartment",
    "budget_min": 800000,
    "budget_max": 1200000,
    "preferred_city": "Dubai",
    "preferred_area": "Dubai Marina",
    "status": "contacted",
    "assigned_agent_id": 5,
    "created_at": "2025-01-22 10:00:00",
    "updated_at": "2025-01-22 14:30:00"
  }
}
```

**cURL Example:**

```bash
curl -X GET http://localhost/api/leads/123 \
  -H "X-API-KEY: dprop_live_4f8a9b2c1d3e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6"
```

#### 4. API Documentation

**GET** `/api/docs`

Get full API documentation in JSON format.

## Routing Engine

The routing engine automatically assigns leads to agents based on:

### 1. Routing Rules (Priority-based)

Rules are evaluated in order of priority (lower number = higher priority):

- **Zone-based**: Match lead's city/area to predefined zones
- **Budget-based**: Route high-value leads to senior agents
- **Property Type-based**: Route luxury properties to specialized agents
- **Source-based**: Route leads from specific portals to dedicated teams

### 2. Assignment Strategies

- **Round Robin**: Distribute leads evenly among agents
- **Least Active**: Assign to agent with fewest active leads
- **Priority-based**: Assign based on agent priority in zone
- **Weighted**: Random selection based on agent weights

### 3. Agent Capacity

Each agent has a maximum active leads limit. The system only assigns to agents with available capacity.

## Subscription System

### Plans

Three default subscription plans:

1. **Starter** ($49/month)
   - 5 agents
   - 100 leads/month
   - 5 zones
   - 5 routing rules

2. **Professional** ($149/month)
   - 20 agents
   - 500 leads/month
   - 20 zones
   - 20 routing rules

3. **Enterprise** ($399/month)
   - 100 agents
   - 5000 leads/month
   - 100 zones
   - 100 routing rules

### Quota Enforcement

The system automatically tracks usage and enforces limits:
- Prevents creating agents beyond plan limit
- Prevents creating leads beyond monthly limit
- Prevents creating zones beyond plan limit
- Prevents creating routing rules beyond plan limit

## CSV Import

Import bulk leads from CSV files:

1. Navigate to Leads → Import
2. Upload CSV file (columns: name, phone, email, city, area, etc.)
3. Map CSV columns to lead fields
4. System automatically routes all imported leads

## Testing

### Manual Testing Checklist

1. **Authentication**
   - [ ] Login with platform admin
   - [ ] Login with tenant admin
   - [ ] Login with agent
   - [ ] Logout

2. **Lead Management**
   - [ ] Create manual lead
   - [ ] View lead details
   - [ ] Update lead status
   - [ ] Add notes to lead
   - [ ] Filter leads by status/source/agent
   - [ ] Verify automatic routing

3. **Agent Management**
   - [ ] Create new agent
   - [ ] Edit agent details
   - [ ] View agent performance metrics
   - [ ] Verify capacity limits

4. **Zone Management**
   - [ ] Create zone
   - [ ] Assign agents to zone
   - [ ] Set agent priorities in zone
   - [ ] Verify zone-based routing

5. **Routing Rules**
   - [ ] Create budget-based rule
   - [ ] Create zone-based rule
   - [ ] Create property-type rule
   - [ ] Verify rule priority execution
   - [ ] Toggle rule active/inactive

6. **API Testing**
   - [ ] Create lead via API
   - [ ] Update lead via API
   - [ ] Verify API authentication
   - [ ] Test invalid API key
   - [ ] Test quota limits

7. **Dashboard & Analytics**
   - [ ] View tenant admin dashboard
   - [ ] View agent dashboard
   - [ ] Verify statistics accuracy
   - [ ] Check agent leaderboard

### Automated Tests

Run the basic test suite:

```bash
php tests/run_tests.php
```

## Security Features

- **Password Hashing**: All passwords hashed using PHP's `password_hash()`
- **CSRF Protection**: All forms protected with CSRF tokens
- **SQL Injection Prevention**: All queries use PDO prepared statements
- **Input Validation**: Server-side validation on all inputs
- **Input Sanitization**: HTML special characters escaped
- **API Authentication**: API key validation for all endpoints
- **Session Security**: Secure session handling
- **XSS Prevention**: All user input escaped in views

## Deployment

### Production Checklist

1. **Environment**
   - [ ] Set `APP_ENV=production` in `.env`
   - [ ] Set `APP_DEBUG=false` in `.env`
   - [ ] Use strong database password
   - [ ] Enable HTTPS (update .htaccess)

2. **Database**
   - [ ] Backup database regularly
   - [ ] Use separate database user with limited privileges
   - [ ] Enable MySQL slow query log for optimization

3. **File Permissions**
   ```bash
   chmod -R 755 storage/
   chmod 644 .env
   ```

4. **Web Server**
   - [ ] Enable mod_security (Apache)
   - [ ] Configure rate limiting
   - [ ] Set up fail2ban
   - [ ] Enable HTTPS with SSL certificate

5. **PHP Configuration**
   ```ini
   display_errors = Off
   log_errors = On
   error_log = /var/log/php_errors.log
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 60
   ```

6. **Monitoring**
   - [ ] Set up error logging
   - [ ] Configure uptime monitoring
   - [ ] Monitor database performance
   - [ ] Set up email notifications for errors

7. **Backups**
   - [ ] Automated daily database backups
   - [ ] Backup `/storage/uploads` directory
   - [ ] Test restore procedure

## Performance Optimization

1. **Database**
   - All tables use InnoDB engine
   - Indexes on foreign keys and frequently queried columns
   - Query optimization using EXPLAIN

2. **Caching**
   - Consider adding OpCode cache (OPcache)
   - Implement query result caching for dashboards
   - Use CDN for static assets

3. **File Uploads**
   - Optimize uploaded images
   - Implement file size limits
   - Use cloud storage for large files

## Troubleshooting

### Common Issues

**Issue**: 404 on all pages except home
- **Solution**: Enable mod_rewrite in Apache or configure nginx properly

**Issue**: Database connection errors
- **Solution**: Verify database credentials in `.env` and ensure MySQL is running

**Issue**: CSRF token validation failed
- **Solution**: Ensure sessions are working properly, check session directory permissions

**Issue**: Lead not being routed
- **Solution**: Check routing rules are active, verify agents have capacity

**Issue**: API returns 401 Unauthorized
- **Solution**: Verify API key is correct and in `X-API-KEY` header

## Contributing

This is a complete demonstration project. For production use:

1. Implement proper SMTP for email notifications
2. Add real payment gateway integration
3. Implement advanced analytics and reporting
4. Add export functionality (PDF, Excel)
5. Implement real-time notifications (WebSockets/Pusher)
6. Add multi-language support
7. Implement advanced search and filtering
8. Add audit logging

## License

This project is provided as-is for educational and demonstration purposes.

## Support

For issues and questions:
- Review the documentation above
- Check the demo data and examples
- Examine the code comments for implementation details

## Credits

Built with:
- PHP 7.0+
- MySQL
- Vanilla JavaScript
- Custom MVC Framework

---

**SplashLeadRouter** - Intelligent Lead Routing for Real Estate Agencies
