# Square1 Store API documentation

## Features
- User registration and authentication
- Product listing and search
- Shopping cart management
- Order creation and history tracking
- Secure API with Sanctum middleware

---

## Setup Instructions

### 1. Clone the repository:
```
git clone <repository-url>
```

### 2. Install dependencies:
```
composer install
```

### 3. Configure environment:
- Copy .env.example to .env:
```
cp .env.example .env
```

- Update your database settings in the .env file.

- Generate the application key:
```
php artisan key:generate
```

### 4. Run application:
```
php artisan serve
```

### 5. Test endpoints
- When testing in Postman, the endpoint should have before the "api/v1/" the localhost URL, which normally is http://127.0.0.1:8000/
- Replace the value of the body for a valid value.
- Adittional query parameters are added after the endpoint in the form of "?key=value&key=value&key=value"
- The endpoints protected by sanctum middleware only can be accesed after logging in and obtaining the token, which has to be pasted in the Headers section of Postman, in the key "Authorization" with value "Bearer ", followed by the full token.

### Authentication Endpoints
**Register:** 
- Endpoint: POST /api/v1/register
- Body:
```
{
  "name": "string",
  "email": "string",
  "password": "string"
}
```

**Login:**
- Endpoint: POST /api/v1/login
- Body:
```
{
  "email": "string",
  "password": "string"
}
```

**Profile:** (Protected by Sanctum)
- Endpoint: GET /api/v1/profile

**Logout:** (Protected by Sanctum)
- Endpoint: POST /api/v1/logout

### Shopping Cart Endpoints
All the following endpoints are Protected by Sanctum.

**View Shopping Cart:**
- Endpoint: GET /api/v1/cart

**Add to Shopping Cart:**
- Endpoint: POST /api/v1/cart/add
- Body:
```
{
  "variant_id": "integer",
  "quantity": "integer"
}
```

**Update Cart Item:**
- Endpoint: PUT /api/v1/cart/update/{CartItemID}
- Body:
```
{
  "variant_id": "integer",
  "quantity": "integer"
}
```

**Delete Cart Item:**
- Endpoint: DELETE /api/v1/cart/remove/{CartItemID}

### Product Endpoints
**List Products:**
- Endpoint: GET /api/v1/products
- Query Parameters: page, per_page
  
**Search Products:**
- Endpoint: GET /api/v1/products/search
- Query Parameters: name, brand, collection, gender, min_price, max_price

**Get Product Details:**
- Endpoint: GET /api/v1/products/{ProductID}    

### Orders Endpoints
All the following endpoints are Protected by Sanctum.

**Create Order:**
- Endpoint: POST /api/v1/orders/create
- Body:
```
{
  "shipping_address": "string",
  "payment_method": "string"
}
```

**List User Orders:**
- Endpoint: GET /api/v1/orders

**Get Order Details:**
- Endpoint: GET /api/v1/orders/{OrderID}
