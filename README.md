# Smart Delivery Route Optimization System

> AI-powered route planning and cost prediction system for logistics operations

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://www.php.net/)
[![Python Version](https://img.shields.io/badge/Python-3.11+-green.svg)](https://www.python.org/)
[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com/)
[![Flask](https://img.shields.io/badge/Flask-3.0-black.svg)](https://flask.palletsprojects.com/)

## Overview

The Smart Delivery Route Optimization System is an integrated web-based platform that combines machine learning cost prediction with metaheuristic route optimization algorithms to improve operational efficiency in logistics operations. Developed as a final year capstone project for BSc Software Engineering at Cardiff Metropolitan University, the system has been validated through deployment at Akvora International (Pvt) Ltd, demonstrating measurable improvements in route efficiency and cost accuracy.

## Key Features

### AI-Powered Cost Prediction
- Random Forest regression model achieving 96.1% prediction accuracy
- Mean Absolute Error of Rs. 512 on production routes
- Confidence scoring with detailed cost breakdowns
- Automatic fallback to historical averages when AI service unavailable

### Intelligent Route Optimization
- Ant Colony Optimization (ACO) algorithm implementation
- Genetic Algorithm (GA) for comparative analysis
- Average route distance improvement of 18.7% in production
- Statistical significance confirmed (p < 0.001)

### Comprehensive Web Application
- Responsive web interface built with Laravel 11 and AdminLTE
- Google Maps API integration for geocoding and distance calculation
- Multi-company cost allocation for shared routes
- Role-Based Access Control (RBAC) with four permission levels

### Analytics and Reporting
- Variance analysis (estimated vs actual costs)
- Driver performance metrics
- Company performance summaries
- Excel export functionality for further analysis

## Technical Architecture

### System Design
The application employs a dual-architecture pattern separating web application logic from AI services:

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTPS
┌──────▼──────┐
│    Nginx    │
└──────┬──────┘
       │
┌──────▼──────┐         REST API        ┌──────────────┐
│   Laravel   │◄─────────────────────────┤    Flask     │
│     App     │                          │  AI Service  │
└──────┬──────┘                          └──────┬───────┘
       │                                        │
┌──────▼──────┐                          ┌──────▼───────┐
│    MySQL    │                          │  ML Models   │
│  Database   │                          │   (.pkl)     │
└─────────────┘                          └──────────────┘
```

### Technology Stack

**Backend:**
- Laravel 11.x (PHP 8.2)
- Flask 3.0 (Python 3.11)
- MySQL 8.0

**Frontend:**
- Blade templating engine
- AdminLTE 3.2 administration theme
- Bootstrap 5 CSS framework
- JavaScript/jQuery

**Machine Learning:**
- scikit-learn 1.3.0
- NumPy 1.24
- Pandas 2.0
- joblib for model serialization

**External Services:**
- Google Maps Geocoding API
- Google Maps Distance Matrix API

## Performance Metrics

### Machine Learning Model
| Metric | Test Set | Production |
|--------|----------|------------|
| R² Score | 96.09% | 96.1% |
| MAE | Rs. 1,377 | Rs. 512 |
| RMSE | Rs. 1,981 | Rs. 1,245 |
| MAPE | 9.78% | 3.9% |

### Route Optimization
| Algorithm | Avg Improvement | Avg Time | Win Rate |
|-----------|----------------|----------|----------|
| ACO | 19.96% | 4.2s | 99/100 |
| GA | 22.32% | 6.8s | 1/100 |

### Business Impact (First Month Production)
- Monthly fuel savings: Rs. 174,650
- Route planning time reduction: 65% (23 min → 8 min)
- Cost prediction errors: -69% (12.5% → 3.9%)
- On-time delivery improvement: +7% (87% → 94%)
- **Total monthly savings: Rs. 248,950**
- **Projected annual ROI: 398%**

## Installation

### Prerequisites
- PHP >= 8.2 with extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON
- Python >= 3.11
- MySQL >= 8.0
- Composer (PHP dependency manager)
- pip (Python package installer)
- Node.js >= 18.x and npm (for asset compilation)

### Laravel Application Setup

```bash
# Clone the repository
git clone https://github.com/YOUR-USERNAME/smart-delivery-optimization.git
cd smart-delivery-optimization/laravel-app

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database connection in .env
# DB_DATABASE=your_database_name
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run database migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed

# Install and compile frontend assets
npm install
npm run build

# Start development server
php artisan serve
```

The Laravel application will be available at `http://localhost:8000`

### Flask AI Service Setup

```bash
# Navigate to Flask directory
cd ../flask-ai-service

# Create virtual environment (recommended)
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate

# Install Python dependencies
pip install -r requirements.txt

# Configure environment variables
cp .env.example .env
# Edit .env to set FLASK_APP, FLASK_ENV, etc.

# Ensure ML models are in correct directory
# Models should be in: app/models/random_forest_model.pkl

# Start Flask server
python app.py
```

The Flask API will be available at `http://localhost:5000`

### Configuration

#### Laravel Configuration
Edit `laravel-app/.env`:
```env
APP_NAME="Smart Delivery System"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delivery_optimization
DB_USERNAME=root
DB_PASSWORD=

AI_SERVICE_URL=http://localhost:5000
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

#### Flask Configuration
Edit `flask-ai-service/.env`:
```env
FLASK_APP=app.py
FLASK_ENV=production
MODEL_PATH=app/models/
SECRET_KEY=your_secret_key_here
```

## Usage

### Creating a Route
1. Navigate to Routes → Create New Route
2. Select date, company, driver, and vehicle
3. Add delivery stops using map search or manual address entry
4. Click "Get AI Prediction" for cost estimate
5. Review prediction with confidence score
6. Save route

### Optimizing Routes
1. Open an existing route
2. Click "Optimize Route" button
3. Select optimization algorithm (ACO or GA)
4. Review before/after comparison
5. Accept optimization if satisfactory
6. Route stop sequence updates automatically

### Generating Reports
1. Navigate to Reports section
2. Select report type (Variance Analysis, Driver Performance, etc.)
3. Set date range filters
4. Click "Generate Report"
5. Export to Excel if needed

## Testing

### Laravel Tests
```bash
cd laravel-app

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run tests with coverage
php artisan test --coverage
```

### Flask Tests
```bash
cd flask-ai-service

# Run all tests
pytest

# Run with coverage
pytest --cov=app tests/

# Run specific test file
pytest tests/test_prediction.py
```

## API Documentation

### Flask AI Service Endpoints

#### POST /api/predict
Predicts route cost based on features.

**Request:**
```json
{
  "distance_km": 127.5,
  "num_stops": 8,
  "sales_value": 450000,
  "vehicle_type": 1,
  "route_complexity": 2,
  "day_of_week": 3,
  "is_weekend": 0
}
```

**Response:**
```json
{
  "predicted_cost": 11517.32,
  "confidence": 94.3,
  "breakdown": {
    "distance_factor": 7200,
    "stops_factor": 2400,
    "base_cost": 1917
  }
}
```

#### POST /api/optimize
Optimizes route stop sequence.

**Request:**
```json
{
  "distance_matrix": [[0, 12.5, 8.3], [12.5, 0, 15.2], [8.3, 15.2, 0]],
  "algorithm": "ACO",
  "parameters": {
    "n_ants": 10,
    "n_iterations": 100
  }
}
```

**Response:**
```json
{
  "optimized_sequence": [0, 2, 1],
  "original_distance": 36.0,
  "optimized_distance": 23.5,
  "improvement_percent": 34.7,
  "execution_time": 4.2
}
```

## Project Structure

```
smart-delivery-optimization/
├── laravel-app/
│   ├── app/
│   │   ├── Http/Controllers/      # Request handlers
│   │   ├── Models/                # Eloquent ORM models
│   │   ├── Services/              # Business logic layer
│   │   └── Providers/             # Service providers
│   ├── database/
│   │   ├── migrations/            # Database schema
│   │   └── seeders/               # Sample data
│   ├── resources/
│   │   └── views/                 # Blade templates
│   ├── routes/
│   │   └── web.php                # Route definitions
│   ├── tests/                     # PHPUnit tests
│   └── public/                    # Public assets
│
├── flask-ai-service/
│   ├── app/
│   │   ├── models/                # ML model files
│   │   ├── optimizers/            # ACO, GA algorithms
│   │   ├── api/                   # REST endpoints
│   │   └── utils/                 # Helper functions
│   ├── tests/                     # Pytest tests
│   └── app.py                     # Application entry point
│
├── ml-notebooks/
│   ├── 01_data_exploration.ipynb
│   ├── 02_data_preprocessing.ipynb
│   ├── 03_cost_prediction_model.ipynb
│   ├── 04_model_optimization.ipynb
│   ├── 05_client_business_analysis.ipynb
│   ├── 06_aco_route_optimization.ipynb
│   └── 07_route_optimization_GA_comparison.ipynb
│
├── database/
│   ├── schema.sql                 # Database creation
│   └── sample_data.sql            # Test data
│
├── docs/
│   ├── API_DOCUMENTATION.md
│   ├── DEPLOYMENT_GUIDE.md
│   └── USER_MANUAL.md
│
└── README.md
```

## Contributing

This project was developed as an academic capstone project and is not actively maintained for external contributions. However, researchers and students are welcome to fork the repository for educational purposes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Citation

If you use this code or methodology in your research, please cite:

```
@mastersthesis{pasindu2026smart,
  author = {Pasindu},
  title = {Smart Delivery Route Optimization System: A Machine Learning Approach for Cost Prediction and Route Efficiency},
  school = {Cardiff Metropolitan University},
  year = {2026},
  type = {BSc Dissertation},
  address = {Cardiff, United Kingdom}
}
```

## Acknowledgments

- **Supervisor:** Mr. Bhagya, Cardiff Metropolitan University
- **Project Coordinator:** Dr. Gayan, Cardiff Metropolitan University
- **Academic Coordinator:** Mrs. Ruwini, Cardiff Metropolitan University
- **Industry Partner:** Akvora International (Pvt) Ltd
- **Special Thanks:** Route coordinators and drivers at Akvora International for participation in user acceptance testing

## Contact

**Author:** Pasindu  
**Institution:** Cardiff Metropolitan University  
**Program:** BSc Software Engineering  
**Email:** [your-email]@cardiffmet.ac.uk  
**Project Year:** 2025-2026  

## References

For academic references and detailed methodology, please refer to the full dissertation document available in the `docs/` directory.

---

**Last Updated:** February 2026  
**Version:** 1.0.0  
**Status:** Production Deployment Complete
