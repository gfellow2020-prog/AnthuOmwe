# Anthu Omwe Mobile

React Native / Expo client for the Anthu Omwe Laravel portal API.

## API base URL

The starter app uses `http://localhost:8000/api/v1` in `App.tsx`. Change `API_BASE_URL` to your Laravel server URL when running on a device or emulator.

## Run

```bash
cd mobile
npm install
npm run android # or npm run ios / npm run web
```

## Included screens

- Staff login with bearer token auth
- Dashboard summary
- Stage queues
- Patient search
- Recent encounters

The app calls the API routes added under `/api/v1/*` and reuses the same staff credentials as the web portal.
