***Endpoints de la API:***

---

# **API Backend para Gestión de Usuarios, Carrito y Órdenes**

Este es un backend RESTful construido en **Laravel** para gestionar usuarios, productos, carritos de compras y órdenes. La API está protegida con **Sanctum** para garantizar que solo los usuarios autenticados puedan acceder a ciertas rutas.

## **Endpoints de la API**

A continuación, se detallan los endpoints disponibles en la API:

### **1. Usuarios (Users)**

#### **POST /register**
- **Descripción**: Registrar un nuevo usuario.
- **Parámetros**:
  - `name` (string): Nombre del usuario.
  - `email` (string): Correo electrónico del usuario.
  - `password` (string): Contraseña del usuario.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan.perez@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
  ```

#### **POST /login**
- **Descripción**: Autenticar al usuario y devolver un token de acceso.
- **Parámetros**:
  - `email` (string): Correo electrónico del usuario.
  - `password` (string): Contraseña del usuario.
- **Respuesta de Éxito**:
  ```json
  {
    "token": "token_generado_aqui"
  }
  ```

#### **GET /profile**
- **Descripción**: Obtener la información del usuario autenticado.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan.perez@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
  ```

#### **POST /logout**
- **Descripción**: Cerrar sesión del usuario autenticado.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "message": "Logged out successfully"
  }
  ```

---

### **2. Carrito de Compras (Shopping Cart)**

#### **GET /cart**
- **Descripción**: Ver el contenido del carrito del usuario autenticado.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "user_id": 1,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "items": []
  }
  ```

#### **POST /cart/add**
- **Descripción**: Agregar un producto/variante al carrito.
- **Parámetros**:
  - `product_variant_id` (int): ID de la variante del producto.
  - `quantity` (int): Cantidad del producto.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "cart_id": 1,
    "product_variant_id": 1,
    "quantity": 2,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
  ```

#### **PUT /cart/update/{CartItemID}**
- **Descripción**: Actualizar la cantidad de un producto en el carrito.
- **Parámetros**:
  - `CartItemID` (int): ID del item en el carrito.
  - `quantity` (int): Nueva cantidad del producto.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "cart_id": 1,
    "product_variant_id": 1,
    "quantity": 5,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
  ```

#### **DELETE /cart/remove/{CartItemID}**
- **Descripción**: Eliminar un producto del carrito.
- **Parámetros**:
  - `CartItemID` (int): ID del item en el carrito.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "message": "Item removed from cart"
  }
  ```

---

### **3. Productos (Products)**

#### **GET /products**
- **Descripción**: Listar todos los productos disponibles con sus variantes.
- **Respuesta de Éxito**:
  ```json
  [
    {
      "id": 1,
      "name": "Camiseta Nike",
      "price": 29.99,
      "variants": [
        {
          "id": 1,
          "color": "Rojo",
          "size": "M",
          "price": 29.99
        },
        {
          "id": 2,
          "color": "Azul",
          "size": "L",
          "price": 29.99
        }
      ]
    }
  ]
  ```

#### **GET /products/{ProductID}**
- **Descripción**: Obtener los detalles de un producto específico.
- **Parámetros**:
  - `ProductID` (int): ID del producto.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "name": "Camiseta Nike",
    "price": 29.99,
    "variants": [
      {
        "id": 1,
        "color": "Rojo",
        "size": "M",
        "price": 29.99
      },
      {
        "id": 2,
        "color": "Azul",
        "size": "L",
        "price": 29.99
      }
    ]
  }
  ```

#### **GET /products/search**
- **Descripción**: Buscar productos por nombre, color, talla, marca, colección, precio o género.
- **Parámetros** (opcional):
  - `name` (string): Nombre del producto.
  - `color` (string): Color del producto.
  - `size` (string): Talla del producto.
  - `brand` (string): Marca del producto.
  - `collection` (string): Colección del producto.
  - `price` (float): Precio del producto.
  - `gender` (string): Género al que pertenece el producto (masculino, femenino, unisex).
- **Respuesta de Éxito**:
  ```json
  [
    {
      "id": 1,
      "name": "Camiseta Nike",
      "price": 29.99,
      "variants": [
        {
          "id": 1,
          "color": "Rojo",
          "size": "M",
          "price": 29.99
        }
      ]
    }
  ]
  ```

---

### **4. Órdenes (Orders)**

#### **POST /orders/create**
- **Descripción**: Crear una orden basada en los productos en el carrito del usuario autenticado.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Validación**: No se puede crear una orden si el carrito está vacío.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "user_id": 1,
    "total": 59.98,
    "status": "pending",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "items": [
      {
        "id": 1,
        "order_id": 1,
        "product_variant_id": 1,
        "quantity": 2,
        "price": 29.99
      }
    ]
  }
  ```

#### **GET /orders**
- **Descripción**: Listar todas las órdenes del usuario autenticado.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de

 Éxito**:
  ```json
  [
    {
      "id": 1,
      "user_id": 1,
      "total": 59.98,
      "status": "pending",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
  ```

#### **GET /orders/{OrderID}**
- **Descripción**: Obtener los detalles de una orden específica.
- **Parámetros**:
  - `OrderID` (int): ID de la orden.
- **Autenticación**: Requiere el token de acceso en el encabezado `Authorization: Bearer token_generado_aqui`.
- **Respuesta de Éxito**:
  ```json
  {
    "id": 1,
    "user_id": 1,
    "total": 59.98,
    "status": "pending",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "items": [
      {
        "id": 1,
        "order_id": 1,
        "product_variant_id": 1,
        "quantity": 2,
        "price": 29.99
      }
    ]
  }
  ```

---

## **Consideraciones Adicionales**

- **Autenticación**: Los endpoints que requieren autenticación deben incluir el token de acceso en el encabezado de la solicitud:
  
  ```http
  Authorization: Bearer token_generado_aqui
  ```

- **Errores**: Asegúrate de manejar correctamente los errores como la falta de autenticación, datos inválidos, o recursos no encontrados.

---

Este archivo te permitirá tener un claro entendimiento sobre cómo interactuar con los endpoints de la API y cómo realizar pruebas usando **Postman**. ¡Listo para empezar!
