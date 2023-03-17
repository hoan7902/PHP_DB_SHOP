# APIs

-   [Login API](#login-api)
-   [Register API](#register-api)
-   [Get Users API](#get-users-api)
-   [Get User By ID API](#get-user-by-id-api)
-   [Update Profile API](#update-profile-api)
-   [Upload A Product API](#upload-a-product-api)
-   [Get A Detail Product API](#get-a-detail-product-api)

# Login API

This API is used to authenticate users and generate access tokens for subsequent requests.

## Request

`POST /api/user/login`

### Headers

| Header       | Description                          |
| ------------ | ------------------------------------ |
| Content-Type | Required. Set to `application/json`. |

### Body

The request body should contain a JSON object with the following properties:

| Property | Type   | Description                         |
| -------- | ------ | ----------------------------------- |
| email    | string | Required. The username of the user. |
| password | string | Required. The password of the user. |

Example request body:

```json
{
    "email": "vanlamcs@vanlam.com",
    "password": "secretPassword"
}
```

## Resonse

If the request is successful, the server will respond with a JSON object containing an access token.

### HTTP Status Codes

| Status Code | Description                     |
| ----------- | ------------------------------- |
| 200         | OK. The request was successful. |
| 400         | Bad Request. Login failed.      |

### Body

| Property | Type    | Description                                                              |
| -------- | ------- | ------------------------------------------------------------------------ |
| status   | boolean | Successful or Failed.                                                    |
| token    | string  | Required. The access token that must be included in subsequent requests. |

Example response body:

```json
{
    "status": true,
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxMCIsInJvbGUiOiJjdXN0b21lciIsImV4cCI6MTY4MTQxMDI4NCwiaWF0IjoxNjc4ODE4Mjg0fQ.a_hTI8iaIgOUoiyTsNeOfXUr41p5QeDYQ3JWjIftRg0"
}
```

# Register API

This API allows users to create a new account on the platform.

## Request

`POST /api/user/register`

### Headers

| Header       | Description                          |
| ------------ | ------------------------------------ |
| Content-Type | Required. Set to `application/json`. |

### Body

The request body should contain a JSON object with the following properties:

| Property | Type   | Description                         |
| -------- | ------ | ----------------------------------- |
| name     | string | Required. The name of the user.     |
| email    | string | Required. The email of the user.    |
| password | string | Required. The password of the user. |

Example request body:

```json
{
    "name": "Le Van Lam",
    "email": "vanlamcs@vanlam.com",
    "password": "secretPassword"
}
```

## Resonse

If the request is successful, the server will respond with a JSON object containing a message confirming that the user's account has been created.

### HTTP Status Codes

| Status Code | Description                                                         |
| ----------- | ------------------------------------------------------------------- |
| 201         | Created. The request was successful and a new resource was created. |
| 400         | Bad Request. Register failed.                                       |

### Body

| Property | Type    | Description           |
| -------- | ------- | --------------------- |
| status   | boolean | Successful or Failed. |

Example response body:

```json
{
    HTTP/1.1 201 Created
    "status": true
}
```

# Get Users API

This API allows admin to get information about users on the platform.

## Request

`GET /api/users/{frame}`

### Headers

| Header        | Description                          |
| ------------- | ------------------------------------ |
| Content-Type  | Required. Set to `application/json`. |
| Authorization | Required. Set to `Bearer <token>`.   |

### Query parameters

| Parameters | Data Type | Description             |
| ---------- | --------- | ----------------------- |
| frame      | integer   | Optional. Default is 1. |

## Resonse

### Successful responses

A successful response returns a JSON object containing an array of registered users in frame.

#### HTTP Status Code

| Status Code | Description |
| ----------- | ----------- |
| 200         | OK.         |

#### Response Body

| Property | Type    | Description                                           |
| -------- | ------- | ----------------------------------------------------- |
| status   | boolean | Successful or Failed.                                 |
| users    | array   | List of users. Each user is represented by an object. |

Example response body:

```json
{
    HTTP/1.1 200 OK
    "status": true,
    "users": [
        {
            "userId": "1",
            "name": "Le Van Lam",
            "phone": null,
            "sex": null,
            "email": "vanlamcs@vanlam.com",
            "avatar": null,
            "address": null,
            "role": "admin"
        },
        {
            "userId": "2",
            "name": "Ho Ngoc An",
            "phone": null,
            "sex": null,
            "email": "ngocan@shop.com",
            "avatar": null,
            "address": null,
            "role": "customer"
        }
    ]
}
```

### Error responses

#### HTTP Status Code

| Status Code | Description                     |
| ----------- | ------------------------------- |
| 401         | Unauthorized. Not Authorization |
| 403         | Forbidden. Not Authentication   |

#### Response Body

| Property | Type    | Description           |
| -------- | ------- | --------------------- |
| status   | boolean | Successful or Failed. |
| message  | string  | Error message.        |

Example response body:

```json
{
    HTTP/1.1 401 Unauthorized
    "status": true,
    "message": "Not Authorization"
}
```

# Get User By ID API

This API allows admin/self to get an information about one user on the platform.

## Request

`GET /api/user/{userId}`

### Headers

| Header        | Description                          |
| ------------- | ------------------------------------ |
| Content-Type  | Required. Set to `application/json`. |
| Authorization | Required. Set to `Bearer <token>`.   |

### Query parameters

There is 1 query parameters required for this request.

Example: `api/user/1`

## Resonse

### Successful responses

A successful response returns a JSON object about user.

#### HTTP Status Code

| Status Code | Description |
| ----------- | ----------- |
| 200         | OK.         |

#### Response Body

| Property | Type    | Description                               |
| -------- | ------- | ----------------------------------------- |
| status   | boolean | Successful or Failed.                     |
| users    | object  | Object containing information about user. |

Example response body:

```json
{
    HTTP/1.1 200 OK
    "status": true,
    "user": {
        "userId": "10",
        "name": "Nguyen Van B",
        "phone": null,
        "sex": null,
        "email": "vanb@gmail.com",
        "avatar": null,
        "address": null
    }
}
```

### Error responses

#### HTTP Status Code

| Status Code | Description                     |
| ----------- | ------------------------------- |
| 401         | Unauthorized. Not Authorization |
| 403         | Forbidden. Not Authentication   |
| 404         | Not Found. User is not valid    |

#### Response Body

| Property | Type    | Description           |
| -------- | ------- | --------------------- |
| status   | boolean | Successful or Failed. |
| message  | string  | Error message.        |

Example response body:

```json
{
    HTTP/1.1 404 Not Found
    "status": true,
    "message": "User is not valid"
}
```

# Update Profile API

This API is used to update information.

## Request

`PUT /api/user/profile`

### Headers

| Header        | Description                          |
| ------------- | ------------------------------------ |
| Content-Type  | Required. Set to `application/json`. |
| Authorization | Required. Set to `Bearer <token>`.   |

### Body

The request body should contain a JSON object with the following properties:

| Property | Type   | Description                                     |
| -------- | ------ | ----------------------------------------------- |
| phone    | string | Optional. The phone number.                     |
| sex      | number | Optional. Sex of the user (1: male, 2: female). |
| avatar   | string | Optional. Avatar of the user.                   |
| name     | string | Optional. The name of the user.                 |
| address  | string | Optional. Address of the user.                  |

Example request body:

```json
{
    "name": "Le VanLam",
    "phone": "0999999888",
    "address": "Loc Son, Phu Loc, Thua Thien - Hue",
    "sex": 0,
    "avatar": "image url"
}
```

## Resonse

If the request is successful, the server will respond with a JSON object containing a status (true or false) and a message.

### HTTP Status Codes

| Status Code | Description                                    |
| ----------- | ---------------------------------------------- |
| 200         | OK. Update successfully.                       |
| 400         | Bad Request. Update failed with error message. |
| 401         | Unauthorized. Not Authorization                |
| 403         | Forbidden. Not Authentication                  |
| 500         | Internal Server Error. Update failed           |

### Body

| Property | Type    | Description           |
| -------- | ------- | --------------------- |
| status   | boolean | Successful or Failed. |
| message  | string  | Just a message :vv.   |

Example response body:

```json
{
    HTTP/1.1 200 OK
    "status": true,
    "message": "Update successful"
}
```

# Upload A Product API

This API allows admin to create a new product.

## Request

`POST /api/product/add`

### Headers

| Header        | Description                          |
| ------------- | ------------------------------------ |
| Content-Type  | Required. Set to `application/json`. |
| Authorization | Required. Set to `Bearer <token>`.   |

### Body

The request body should contain a JSON object with the following properties:

| Property    | Type   | Description                                                                              |
| ----------- | ------ | ---------------------------------------------------------------------------------------- |
| name        | string | Required. The name of the product.                                                       |
| description | string | Required. Product's description .                                                        |
| sizes       | array  | Required. Includes objects containing the following fields: `sizeName, quantity, price`. |
| images      | array  | Required. Includes a list of url images.                                                 |
| categories  | array  | Optional. Includes a list of integer number which are `categoryId`s.                     |

Example request body:

```json
{
    "name": "T-Shirt --LAM--",
    "description": "Description.",
    "categories": [1, 2],
    "sizes": [
        {
            "sizeName": "S",
            "quantity": 12,
            "price": 230000
        },
        {
            "sizeName": "M",
            "quantity": 13,
            "price": 235000
        },
        {
            "sizeName": "L",
            "quantity": 6,
            "price": 240000
        }
    ],
    "images": [
        "image url 1",
        "image url 2",
        "image url 3",
        "image url 4",
        "image url 5",
        "image url 6"
    ]
}
```

## Resonse

### Success response

If the request is successful, the server will respond with a JSON object containing a message about success or fail.

#### HTTP Status Codes

| Status Code | Description                                                         |
| ----------- | ------------------------------------------------------------------- |
| 201         | Created. The request was successful and a new resource was created. |
| 400         | Bad Request. Request failed and return message.                     |

#### Response Body

| Property | Type    | Description            |
| -------- | ------- | ---------------------- |
| status   | boolean | Successful or Failed.  |
| message  | string  | Just an inform string. |

Example response body:

```json
{
    HTTP/1.1 201 OK
    "status": true,
    "message": "Post successful"

}
```

### Error responses

#### HTTP Status Code

| Status Code | Description                        |
| ----------- | ---------------------------------- |
| 400         | Bad Request. Throw message error   |
| 401         | Unauthorized. Not Authorization    |
| 403         | Forbidden. Not Authentication      |
| 500         | Internal Server Error. Post failed |

#### Response Body

| Property | Type    | Description    |
| -------- | ------- | -------------- |
| status   | boolean | False. :<<     |
| message  | string  | Error message. |

Example response body:

```json
{
    HTTP/1.1 403 Forbidden
    "status": true,
    "message": "Not Authentication"
}
```

# Get A Detail Product API

This API allows the user to retrieve detailed information about a product in the platform.

## Request

`GET /api/product/{productId}`

### Headers

| Header | Description |
| ------ | ----------- |
|        |             |

### Query parameters

There is 1 query parameters required for this request.

Example: `api/product/321`

## Resonse

### Successful responses

A successful response returns a JSON object with detailed information about the requested product.

#### HTTP Status Code

| Status Code | Description |
| ----------- | ----------- |
| 200         | OK.         |

#### Response Body

| Property    | Type    | Description                                                |
| ----------- | ------- | ---------------------------------------------------------- |
| status      | boolean | Successful or Failed.                                      |
| productId   | string  | Product ID.                                                |
| description | string  | Product's description                                      |
| sizes       | array   | Array containing objects (`sizeName, quantity, price`)     |
| categories  | array   | Array containing objects (`categoryId, name, description`) |

Example response body:

```json
{
    HTTP/1.1 200 OK
    "status": true,
    "productId": "23",
    "name": "T-Shirt --LAM--",
    "description": "This is a description.",
    "sizes": [
        {
            "sizeName": "L",
            "quantity": "6",
            "price": "240000"
        },
        {
            "sizeName": "M",
            "quantity": "13",
            "price": "235000"
        },
        {
            "sizeName": "S",
            "quantity": "12",
            "price": "230000"
        }
    ],
    "images": [
        "images link 1",
        "images link 2",
        "images link 3",
        "images link 4",
        "images link 5"
    ],
    "categories": [
    {
      "categoryId": "1",
      "name": "Shirt",
      "description": "Nothing"
    },
    {
      "categoryId": "2",
      "name": "Pants",
      "description": "Nothing"
    }
  ]
}
```

### Error responses

#### HTTP Status Code

| Status Code | Description                                |
| ----------- | ------------------------------------------ |
| 400         | Not Found. User does not exist             |
| 500         | Internal Server Error. Get Prodduct Failed |

#### Response Body

| Property | Type    | Description           |
| -------- | ------- | --------------------- |
| status   | boolean | Successful or Failed. |
| message  | string  | Error message.        |

Example response body:

```json
{
    HTTP/1.1 400 Not Found
    "status": true,
    "message": "User does not exist"
}
```

# Remove Product Out Of Category API

...

# Create A Category API

...

# Get Categories API

...
