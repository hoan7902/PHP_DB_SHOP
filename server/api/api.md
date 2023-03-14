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

# Get All Users API

This API allows admin to get all information about users on the platform.

## Request

`GET /api/users`

### Headers

| Header        | Description                          |
| ------------- | ------------------------------------ |
| Content-Type  | Required. Set to `application/json`. |
| Authorization | Required. Set to `Bearer <token>`.   |

### Query parameters

There are no query parameters required for this request.

## Resonse

### Successful responses

A successful response returns a JSON object containing an array of all registered users.

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
| error    | string  | Error message.        |

Example response body:

```json
{
    HTTP/1.1 401 Unauthorized
    "status": true,
    "error": "Not Authorization"
}
```

# Get Users By ID API

This API allows admin/self to get an information about one user on the platform.

## Request

`GET /api/user/:id`

### Headers

| Header        | Description                          |
| ------------- | ------------------------------------ |
| Content-Type  | Required. Set to `application/json`. |
| Authorization | Required. Set to `Bearer <token>`.   |

### Query parameters

There are 1 query parameters required for this request.

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
| error    | string  | Error message.        |

Example response body:

```json
{
    HTTP/1.1 404 Not Found
    "status": true,
    "error": "User is not valid"
}
```
