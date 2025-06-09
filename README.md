# NSSM Service Manager (Svelte)

This project is a simple web UI for managing Windows services via an NSSM based API.  It was converted from a single HTML file using Alpine.js to a Svelte application.

## Development

1. Install dependencies

```bash
npm install
```

2. Start the dev server

```bash
npm run dev
```

The application expects an API server running on `http://127.0.0.1:54321` by default.  You can change this from the dropdown in the UI.

## Building

```bash
npm run build
```

The output will be placed in the `dist` folder which can be served as static files.

## Functionality

- Login with username `admin` and password `admin`
- View available services
- Start, stop and remove services (after login)
- Install a new service
- Automatic refresh interval

All functionality from the original HTML page has been preserved.
