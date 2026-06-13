# busbox — Plan del Proyecto

> Sistema comunitario de rutas de camión para Cancún.
> **"La información es poder"**: que la gente sepa qué transporte la lleva de un punto a otro, sin terminar en Rancho Viejo cuando iba a Jardines del Sur.

Documento vivo. Aquí se guardan las decisiones, el alcance y el modelo de datos. Se actualiza conforme avanza el proyecto.

---

## 1. Visión

App móvil-primero, enfocada en **el pueblo y su gente** de Cancún, que responde:

> **"¿Qué camión me lleva de aquí a allá, cuánto cuesta y cuándo pasa?"**

Construida sobre el starter kit **Laravel 13 + Vue 3 + Inertia v3 + PrimeVue 4 + Tailwind v4** (auth con Fortify, theming y panel ya incluidos). Eventualmente puede expandirse a otras líneas de negocio, pero el foco actual es la comunidad.

---

## 2. Los 3 niveles

| Nivel | Para quién | Qué entrega | Fase |
|-------|-----------|-------------|------|
| 🟢 **Estándar** | Toda la comunidad | Tipos de unidad, horarios, rutas, tarifas y **buscar viaje** origen→destino | **Fase 1 (MVP)** |
| 🔵 **Plus** | Usuario que quiere más | + **GPS en tiempo real** y tiempo estimado de llegada (ETA) a tu parada | Fase 2 |
| 🟠 **Operadores** | Choferes y líneas de camión | Gestionar sus rutas/horarios y **emitir su ubicación GPS** (alimenta el tiempo real del Plus) | Fase 2 |

---

## 3. Decisiones cerradas

| Tema | Decisión |
|------|----------|
| **Login usuario final** | **Cuenta opcional.** Se usa sin registro; la cuenta permite guardar favoritos (paradas/rutas) y, más adelante, Plus. |
| **Selección origen/destino** | **Mi ubicación actual (GPS del navegador) + autocomplete de texto.** Ej: escribo "Palacio Municipal" y se autocompleta. |
| **Autocomplete / Places** | Estrategia de 3 capas: (1) Geolocation API nativa para "mi ubicación"; (2) **autocomplete interno** sobre nuestras paradas/colonias (gratis, offline); (3) **LocationIQ** (free tier 5k/día, OSM) como geocoding externo de respaldo. Desacoplable. |
| **Mapas** | **Leaflet + OpenStreetMap** (sin costos de licencia, sin tarjeta). |
| **Origen de datos** | **Carga manual por admin** + **seeders con rutas inventadas de Cancún** para desarrollo. |
| **Alcance geográfico Fase 1** | **Cancún ciudad (colonias) + zona hotelera.** |
| **Plataforma** | Web responsive móvil-primero (PWA en Fase 2 para offline). |

---

## 4. Supuestos / valores por defecto (Fase 1)

Decisiones tomadas por defecto para no frenar el MVP. Ajustables.

- **Direccionalidad:** cada ruta tiene dirección **ida** y **vuelta** (campo `direction` en el pivote `route_stop`). Rutas circulares = una sola dirección.
- **Tarifa:** **fija por ruta** (un solo monto). Tarifas variables/por tramo quedan para después.
- **Búsqueda de viaje:** solo **ruta directa** en Fase 1 (origen y destino servidos por la misma ruta y dirección). **Transbordos** (combinar 2+ rutas) = Fase 2.
- **Roles:** columna simple `role` en `users` (`user` | `admin` | `operator`) — sin dependencia externa. Si crece la necesidad de permisos finos, se evaluará `spatie/laravel-permission`.
- **Consultas geográficas:** se usa `matanyadaev/laravel-eloquent-spatial` para columnas espaciales y consultas de "parada más cercana" / geometría de trazos.
- **Idioma:** Español. Bilingüe se evaluará si entra el ángulo turístico.

---

## 5. Modelo de datos — Fase 1

Inspirado en **GTFS** (estándar mundial de transporte) para facilitar futura interoperabilidad (Google Maps, Moovit, etc.).

### `routes` (rutas)
| Campo | Tipo | Notas |
|-------|------|-------|
| `id` | id | |
| `code` | string | Ej: "R-1" |
| `name` | string | Ej: "Centro – Zona Hotelera" |
| `color` | string | Hex para dibujar en mapa |
| `vehicle_type` | enum/string | `urban`, `camion`, `combi` |
| `fare` | decimal | Tarifa fija |
| `frequency_minutes` | int (nullable) | Cada cuánto pasa |
| `operating_hours` | string/json | Ej: "05:00–23:00" |
| `is_active` | bool | |
| `description` | text (nullable) | |

### `stops` (paradas)
| Campo | Tipo | Notas |
|-------|------|-------|
| `id` | id | |
| `name` | string | Ej: "Palacio Municipal" |
| `lat` / `lng` | decimal | Geolocalización |
| `landmark` | string (nullable) | Referencia: "frente al Chedraui" |
| `colonia` | string (nullable) | Para autocomplete por zona |
| `is_landmark` | bool | Marca puntos clave (ADO, mercados, Palacio) |

### `route_stop` (pivote, ordenado)
| Campo | Tipo | Notas |
|-------|------|-------|
| `route_id` | fk | |
| `stop_id` | fk | |
| `sequence` | int | Orden de la parada en la ruta |
| `direction` | enum | `ida` / `vuelta` |

### `route_paths` (opcional — trazo del recorrido)
| Campo | Tipo | Notas |
|-------|------|-------|
| `route_id` | fk | |
| `direction` | enum | `ida` / `vuelta` |
| `geometry` | json | Polyline de coordenadas para dibujar el camino real |

### Relaciones
- `Route` ⇄ `Stop` (muchos a muchos vía `route_stop`, con `sequence` y `direction`).
- `Route` → `RoutePath` (uno por dirección).
- `User` puede tener favoritos (paradas/rutas) — tabla `favorites` cuando se implemente.

### Datos como DTO (spatie/laravel-data)
Definir `RouteData`, `StopData` → se generan tipos TypeScript automáticamente en `resources/js/types/generated.d.ts` (el kit ya lo hace).

---

## 6. Lógica de "buscar viaje" (Fase 1 — ruta directa)

1. Usuario fija **origen** (GPS → parada más cercana, o autocomplete) y **destino**.
2. Se buscan rutas donde origen y destino estén en `route_stop` con la **misma `route_id` y `direction`**, y `sequence(origen) < sequence(destino)`.
3. Resultado: ruta(s) directa(s) con dónde subir, dónde bajar, paradas intermedias, tarifa, frecuencia y horario.
4. Mapa muestra el trazo y las paradas relevantes.

---

## 7. Pantallas clave (Fase 1)

| Pantalla | Descripción | Acceso |
|----------|-------------|--------|
| **Inicio / Buscar viaje** | Origen + destino (GPS/autocomplete) → resultados | Público |
| **Resultados** | Lista de rutas directas con detalle y mapa | Público |
| **Detalle de ruta** | Recorrido en mapa, paradas, horarios, tarifa, tipo de unidad | Público |
| **Explorar mapa** | Todas las paradas/rutas cerca de mí | Público |
| **Favoritos** | Paradas y rutas guardadas | Con cuenta |
| **Admin: Rutas** | CRUD de rutas, paradas y orden de paradas | Admin |

---

## 7b. Librerías

El kit ya cubre casi todo. Dependencias **nuevas** aprobadas para el MVP:

| Librería | Tipo | Para qué |
|----------|------|----------|
| `leaflet` + `@vue-leaflet/vue-leaflet` + `@types/leaflet` | npm | Mapas interactivos (OpenStreetMap, sin costo) |
| `matanyadaev/laravel-eloquent-spatial` | composer | Columnas espaciales, "parada más cercana", geometría de trazos |

Ya disponibles en el kit (sin instalar nada): **VueUse** (`useGeolocation`), **PrimeVue** (`AutoComplete`), **spatie/laravel-data** (DTO → TS).

No se añaden por ahora (se evaluarán si un dolor concreto lo justifica): `spatie/laravel-permission` (roles = columna simple).

---

## 8. Roadmap

### ✅ Fase 1 — MVP (PRIORIDAD)
Nivel **Estándar** funcionando con rutas reales/seeders.
- [x] Migraciones + modelos (`Route`, `Stop`, `route_stop`, `route_paths`)
- [x] DTOs (`RouteData`, `StopData`) + factories + seeders Cancún
- [ ] Panel admin para cargar rutas/paradas
- [x] Buscar viaje directo (backend)
- [ ] UI buscar + resultados + detalle de ruta
- [ ] Mapa con Leaflet + OSM (paradas y trazo)
- [ ] Geolocation + autocomplete interno
- [ ] Favoritos (cuenta opcional)

**Regla de oro:** mejor 3 rutas reales perfectas que 50 a medias.

### 🔜 Fase 2 — Crecimiento (Plus + Operadores)
- Tiempo real **colaborativo** (pasajero comparte ubicación: "voy a bordo") → estima posición del camión sin hardware.
- App/rol de **operadores** que emite GPS para precisión total.
- **ETA** a tu parada.
- **PWA + offline** (consultar rutas sin datos móviles).
- Transbordos (rutas combinadas) y geocoding externo (LocationIQ) para direcciones libres.

---

## 8b. Fases de desarrollo (build order del MVP)

Cada fase deja algo **funcional y testeado** antes de pasar a la siguiente. Construimos el flujo de texto primero y lo enriquecemos con mapas después. El admin va casi al final porque los seeders ya proveen datos para trabajar.

| # | Fase | Estado | Entregable | Incluye |
|---|------|--------|-----------|---------|
| **0** | Cimientos | ✅ Hecho | Proyecto listo | Instalar Leaflet + eloquent-spatial, columna `role` + helper, verificar tooling |
| **1** | Datos + seeders | ✅ Hecho | Base poblada | Migraciones (`routes`, `stops`, `route_stop`, `route_paths`), modelos + relaciones, DTOs, factories y **seeder con rutas inventadas de Cancún** (ciudad + zona hotelera). Tests de relaciones/seeder |
| **2** | Backend de consulta | ✅ Hecho | Lógica lista | Listar rutas, detalle, **buscar viaje directo** (servicio TripPlanner), parada más cercana (spatial), autocomplete interno. Feature tests |
| **3** | UI pública (texto) | 🔨 En curso | Flujo usable | Inicio/buscar (GPS + AutoComplete), resultados, detalle de ruta — sin mapa aún. Smoke tests |
| **4** | Mapas | ⏳ Pendiente | Visual | Componente mapa reusable (Leaflet + `ClientOnly`), paradas y trazo de ruta, explorar mapa. Integrado en detalle/resultados |
| **5** | Favoritos | ⏳ Pendiente | Cuenta opcional | Tabla `favorites`, guardar/quitar paradas y rutas, UI. Tests |
| **6** | Admin | ⏳ Pendiente | Gestión | CRUD de rutas/paradas + ordenar paradas, sobre el dashboard existente. Tests |
| **7** | Pulido MVP | ⏳ Pendiente | Listo para usar | Responsive móvil, estados vacíos/carga, manejo de GPS denegado, lint/pint/larastan |

---

## 9. Stack e integraciones

- **Backend:** Laravel 13, PHP 8.4, MySQL (`busbox`), Inertia v3.
- **Frontend:** Vue 3, PrimeVue 4, Tailwind v4, Lucide icons.
- **Datos tipados:** spatie/laravel-data → TypeScript transformer.
- **Mapas:** Leaflet + `@vue-leaflet/vue-leaflet` + OpenStreetMap.
- **Geoconsultas:** matanyadaev/laravel-eloquent-spatial.
- **Ubicación:** Geolocation API del navegador (VueUse).
- **Geocoding externo (Fase 2):** LocationIQ (free tier).
- **Calidad:** Pest 4, Larastan, Pint, ESLint.

---

## 10. Próximo paso

**Fase de desarrollo 3 (UI pública – texto):** páginas Vue/Inertia para buscar viaje (GPS + AutoComplete de PrimeVue consumiendo `api/stops/search` y `api/stops/nearest`), resultados de viaje (`api/trips/search`) y detalle de ruta (`api/routes/{route}`). Sin mapa todavía. Smoke tests.

### Endpoints disponibles (Fase 2)
| Método | URI | Para qué |
|--------|-----|----------|
| GET | `api/routes` | Listar rutas activas |
| GET | `api/routes/{route}` | Detalle: paradas + trazo por dirección |
| GET | `api/stops/search?q=` | Autocomplete de paradas/colonias/landmarks |
| GET | `api/stops/nearest?lat=&lng=` | Paradas más cercanas (spatial) |
| GET | `api/trips/search?origin=&destination=` | Viajes directos entre dos paradas |
