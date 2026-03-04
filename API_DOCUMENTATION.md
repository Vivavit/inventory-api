# Complete API Documentation with Role & Permission Protection

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication

All protected endpoints require a valid Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## Public Endpoints (No Authentication Required)

### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login user
- `POST /auth/verify-email` - Verify email address

### Products (Read-Only)
- `GET /products` - List all products
- `GET /products/{id}` - Get single product

### Categories (Read-Only)
- `GET /categories` - List all categories
- `GET /categories/{id}` - Get single category

### Brands (Read-Only)
- `GET /brands` - List all brands
- `GET /brands/{id}` - Get single brand

### Reviews (Read-Only)
- `GET /reviews` - List all reviews
- `GET /reviews/{id}` - Get single review

### Customers (Read-Only)
- `GET /customers` - List all customers
- `GET /customers/{id}` - Get single customer

---

## Protected Endpoints (Authentication Required)

### Authentication (All Users)
- `GET /auth/me` - Get current user profile
- `PUT /auth/profile` - Update profile
- `POST /auth/change-password` - Change password
- `POST /auth/refresh` - Refresh token
- `POST /auth/logout` - Logout from current device
- `POST /auth/logout-all` - Logout from all devices

### User Management (Role: Admin)
- `GET /users` - List all users
- `POST /users` - Create user
- `GET /users/{id}` - Get user details
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user

### User Roles & Permissions (Role: Admin)
- `GET /users/{id}/roles` - Get user roles
- `GET /users/{id}/permissions` - Get user permissions
- `POST /users/{id}/assign-role` - Assign role to user
- `POST /users/{id}/revoke-role` - Revoke role from user
- `POST /users/{id}/sync-roles` - Sync user roles
- `POST /users/{id}/check-permission` - Check user permission
- `POST /users/{id}/check-role` - Check user role

### Role Management (Role: Admin)
- `GET /roles` - List all roles
- `POST /roles` - Create role
- `GET /roles/{id}` - Get role details
- `PUT /roles/{id}` - Update role
- `DELETE /roles/{id}` - Delete role
- `POST /roles/{id}/assign-permission` - Assign permission to role
- `POST /roles/{id}/revoke-permission` - Revoke permission from role

### Permission Management (Role: Admin)
- `GET /permissions` - List all permissions
- `POST /permissions` - Create permission
- `GET /permissions/{id}` - Get permission details
- `PUT /permissions/{id}` - Update permission
- `DELETE /permissions/{id}` - Delete permission

### Product Management
- `POST /products` - Create product (Permission: `create_products`)
- `PUT /products/{id}` - Update product (Permission: `edit_products`)
- `DELETE /products/{id}` - Delete product (Permission: `delete_products`)

### Category Management
- `POST /categories` - Create category (Permission: `create_categories`)
- `PUT /categories/{id}` - Update category (Permission: `edit_categories`)
- `DELETE /categories/{id}` - Delete category (Permission: `delete_categories`)

### Brand Management
- `POST /brands` - Create brand (Permission: `create_brands`)
- `PUT /brands/{id}` - Update brand (Permission: `edit_brands`)
- `DELETE /brands/{id}` - Delete brand (Permission: `delete_brands`)

### Order Management (Permission: `manage_orders`)
- `GET /orders` - List all orders
- `POST /orders` - Create order
- `GET /orders/{id}` - Get order details
- `PUT /orders/{id}` - Update order
- `DELETE /orders/{id}` - Delete order

### Customer Management
- `POST /customers` - Create customer (Permission: `create_customers`)
- `PUT /customers/{id}` - Update customer (Permission: `edit_customers`)
- `DELETE /customers/{id}` - Delete customer (Permission: `delete_customers`)

### Review Management
- `POST /reviews` - Create review (Permission: `create_reviews`)
- `PUT /reviews/{id}` - Update review (Permission: `edit_reviews`)
- `DELETE /reviews/{id}` - Delete review (Permission: `delete_reviews`)

### Coupon Management (Permission: `manage_coupons`)
- `GET /coupons` - List all coupons
- `POST /coupons` - Create coupon
- `GET /coupons/{id}` - Get coupon details
- `PUT /coupons/{id}` - Update coupon
- `DELETE /coupons/{id}` - Delete coupon

### Address Management (Permission: `manage_addresses`)
- `GET /addresses` - List all addresses
- `POST /addresses` - Create address
- `GET /addresses/{id}` - Get address details
- `PUT /addresses/{id}` - Update address
- `DELETE /addresses/{id}` - Delete address

---

## Default Roles and Permissions

### Admin Role
- Has all permissions

### Editor Role
Permissions:
- `view_products`
- `create_products`
- `edit_products`
- `view_categories`
- `create_categories`
- `edit_categories`
- `view_brands`
- `view_orders`
- `view_customers`
- `create_reviews`
- `edit_reviews`
- `view_reviews`
- `view_coupons`
- `view_addresses`

### Customer Role
Permissions:
- `view_products`
- `view_categories`
- `view_brands`
- `create_reviews`
- `edit_reviews`
- `view_reviews`
- `manage_addresses`

### Seller Role
Permissions:
- `view_products`
- `create_products`
- `edit_products`
- `view_categories`
- `view_brands`
- `view_orders`
- `view_customers`
- `view_reviews`
- `manage_coupons`
- `view_coupons`
- `view_addresses`

---

## Error Responses

### Unauthorized (401)
```json
{
  "status": "error",
  "message": "Unauthorized"
}
```

### Forbidden (403)
```json
{
  "status": "error",
  "message": "Forbidden: You do not have the required role/permission"
}
```

### Validation Error (422)
```json
{
  "status": "error",
  "message": "Validation error",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## Success Response Format

All successful responses follow this format:

```json
{
  "status": "success",
  "message": "Action description",
  "data": {
    // Response data
  }
}
```
