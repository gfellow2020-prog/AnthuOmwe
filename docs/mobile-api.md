# Mobile API

The React Native app in `mobile/` uses the JSON API registered in `routes/api.php`.

## Authentication

```
POST /api/v1/auth/login
GET  /api/v1/auth/me
POST /api/v1/auth/logout
```

`login` accepts the same staff email/password used by the web portal and returns a bearer token. Store the token securely on device and send it on subsequent requests:

```
Authorization: Bearer <token>
Accept: application/json
```

Tokens are stored hashed in `user_api_tokens` and revoked on logout.

## First API surface

- `GET /api/v1/dashboard/summary`
- `GET /api/v1/patients`
- `GET /api/v1/patients/search?q=...`
- `GET /api/v1/patients/{ref}`
- `GET /api/v1/registration/search-patient`
- `GET /api/v1/registration/search-households`
- `GET|POST /api/v1/villages`
- `GET|POST /api/v1/encounters`
- `GET /api/v1/encounters/{encounter}`
- `POST /api/v1/encounters/{encounter}/queue/triage`
- `GET /api/v1/queues/{stage}`
- Triage, screening, lab, screening review, and pharmacy receive/complete/action endpoints
- `GET /api/v1/medications`
- `GET /api/v1/notifications`
- `POST /api/v1/notifications/{notification}/read`
- `POST /api/v1/notifications/read-all`

The API controllers reuse the same encounter actions and FormRequests as the Blade portal so mobile and web stay aligned.
