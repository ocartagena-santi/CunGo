# CunGo

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Inertia](https://img.shields.io/badge/Inertia-v3-3068F6?logo=inertia&logoColor=white)](https://inertiajs.com)
[![Vue](https://img.shields.io/badge/Vue-3-42B883?logo=vue.js&logoColor=white)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-v4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

Sistema comunitario de rutas de camión para Cancún — web responsive móvil-primero que responde: **"¿Qué camión me lleva de aquí a allá, cuánto cuesta y cuándo pasa?"**

## Stack

| Capa | Tecnología |
|------|------------|
| **Backend** | Laravel 13, PHP 8.4, MySQL |
| **Frontend** | Vue 3, PrimeVue 4, Tailwind v4, Lucide icons |
| **SPA bridge** | Inertia.js v3 |
| **Auth** | Laravel Fortify (cuenta opcional) |
| **Mapas** | Leaflet + OpenStreetMap (`@vue-leaflet/vue-leaflet`) |
| **Datos tipados** | spatie/laravel-data → TypeScript transformer |
| **Geoconsultas** | matanyadaev/laravel-eloquent-spatial |
| **Ubicación** | Geolocation API del navegador (VueUse) |
| **Calidad** | Pest 4, Larastan, Pint, ESLint |

## Funcionalidades (Fase 1 — MVP)

- **Buscar viaje:** origen (GPS / autocomplete) + destino → rutas directas con paradas, tarifa, frecuencia y horario.
- **Detalle de ruta:** recorrido en mapa, paradas ordenadas, tipo de unidad, tarifas.
- **Explorar mapa:** todas las paradas y rutas cerca del usuario.
- **Favoritos:** guardar paradas y rutas (requiere cuenta).
- **Panel admin:** CRUD de rutas y paradas *(en desarrollo)*.

## Modelo de datos

Inspirado en **GTFS** (estándar mundial de transporte).

```
routes       — código, nombre, tarifa, horario, tipo de vehículo, color
stops        — nombre, lat/lng, colonia, referencia (landmark)
route_stop   — pivote ordenado (sequence + direction: ida/vuelta)
route_paths  — polyline GeoJSON del trazo real por dirección
users        — auth Fortify + rol (user | admin | operator)
```

## Roles

| Rol | Acceso |
|-----|--------|
| `user` | Consulta rutas, guarda favoritos |
| `admin` | CRUD completo de rutas y paradas |
| `operator` | *(Fase 2)* Emite ubicación GPS en tiempo real |

## Requisitos

- PHP 8.4+
- Node.js 22+
- MySQL 8+

## Instalación

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

## Desarrollo

```bash
composer run dev   # inicia Laravel + Vite en paralelo
```

## Tests

```bash
php artisan test --compact
```

## Roadmap

| Fase | Estado | Descripción |
|------|--------|-------------|
| 0 — Cimientos | ✅ | Setup, Leaflet, eloquent-spatial, roles |
| 1 — Datos | ✅ | Migraciones, modelos, DTOs, factories, seeders Cancún |
| 2 — Backend consulta | ✅ | TripPlanner, parada más cercana, autocomplete |
| 3 — UI pública (texto) | ✅ | Buscar, resultados, detalle de ruta |
| 4 — Mapas | ✅ | Mapa interactivo con paradas y trazos |
| 5 — Favoritos | 🔨 | Guardar paradas y rutas (cuenta opcional) |
| 6 — Admin | ⏳ | CRUD de rutas y paradas |
| 7 — Pulido MVP | ⏳ | Responsive, estados vacíos, manejo GPS denegado |
| **Fase 2** | ⏳ | GPS en tiempo real, ETA, transbordos, PWA offline |
