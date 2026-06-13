<script setup lang="ts">
import { computed, defineAsyncComponent, ref } from 'vue'
import { Link, useHttp } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import { ArrowDown, ArrowRightLeft, Bus, LocateFixed, Search as SearchIcon } from '@lucide/vue'
import TransitLayout from '@/layouts/TransitLayout.vue'
import StopAutocomplete from '@/components/StopAutocomplete.vue'
import ClientOnly from '@/components/ClientOnly.vue'
import { route } from '@/utils/route'
import { directionLabel, formatFare, vehicleTypeLabel } from '@/utils/transit'

const RouteMap = defineAsyncComponent(() => import('@/components/RouteMap.vue'))

type Stop = App.Data.StopData
type Trip = App.Data.DirectTripData

const toast = useToast()

const origin = ref<Stop | string | null>(null)
const destination = ref<Stop | string | null>(null)
const locating = ref(false)
const searched = ref(false)

const trips = ref<Trip[]>([])
const tripsHttp = useHttp<{ origin: number | null, destination: number | null }, Trip[]>({
    origin: null,
    destination: null,
})
const nearestHttp = useHttp<{ lat: number | null, lng: number | null }, Stop[]>({ lat: null, lng: null })

function asStop(value: Stop | string | null): Stop | null {
    return value && typeof value === 'object' ? value : null
}

const canSearch = computed(() => Boolean(asStop(origin.value) && asStop(destination.value)))

const firstTrip = computed<Trip | null>(() => trips.value[0] ?? null)
const firstTripPath = computed<App.Data.LatLngData[]>(() => (
    firstTrip.value?.segmentStops.map((s) => ({ lat: s.lat, lng: s.lng })) ?? []
))

function swap() {
    [origin.value, destination.value] = [destination.value, origin.value]
}

function useMyLocation() {
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
        toast.add({ severity: 'warn', summary: 'Ubicación no disponible', detail: 'Tu navegador no permite geolocalización.', life: 4000 })

        return
    }

    locating.value = true
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            nearestHttp.lat = position.coords.latitude
            nearestHttp.lng = position.coords.longitude
            const stops = await nearestHttp.get(route('api.stops.nearest'))

            if (stops.length > 0) {
                origin.value = stops[0]
                toast.add({ severity: 'success', summary: 'Parada más cercana', detail: stops[0].name, life: 3000 })
            }

            locating.value = false
        },
        () => {
            locating.value = false
            toast.add({ severity: 'warn', summary: 'No pudimos ubicarte', detail: 'Activa el permiso de ubicación e intenta de nuevo.', life: 4000 })
        },
    )
}

async function search() {
    const from = asStop(origin.value)
    const to = asStop(destination.value)

    if (!from || !to) {
        return
    }

    tripsHttp.origin = from.id
    tripsHttp.destination = to.id
    trips.value = await tripsHttp.get(route('api.trips.search'))
    searched.value = true
}
</script>

<template>
    <TransitLayout title="Buscar viaje">
        <div class="max-w-2xl mx-auto space-y-6">
            <header class="text-center space-y-2">
                <h1 class="text-2xl md:text-3xl font-bold">
                    ¿A dónde vas?
                </h1>
                <p class="text-muted-color">
                    Conoce qué camión te lleva a tu destino en Cancún.
                </p>
            </header>

            <Card>
                <template #content>
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label
                                for="origin"
                                class="text-sm font-medium"
                            >Origen</label>
                            <div class="flex gap-2">
                                <StopAutocomplete
                                    v-model="origin"
                                    input-id="origin"
                                    placeholder="¿Dónde estás?"
                                    class="flex-1"
                                />
                                <Button
                                    severity="secondary"
                                    outlined
                                    :loading="locating"
                                    aria-label="Usar mi ubicación"
                                    @click="useMyLocation"
                                >
                                    <template #icon>
                                        <LocateFixed class="size-4" />
                                    </template>
                                </Button>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <Button
                                severity="secondary"
                                text
                                rounded
                                aria-label="Intercambiar origen y destino"
                                @click="swap"
                            >
                                <template #icon>
                                    <ArrowRightLeft class="size-4" />
                                </template>
                            </Button>
                        </div>

                        <div class="space-y-1">
                            <label
                                for="destination"
                                class="text-sm font-medium"
                            >Destino</label>
                            <StopAutocomplete
                                v-model="destination"
                                input-id="destination"
                                placeholder="¿A dónde vas?"
                            />
                        </div>

                        <Button
                            label="Buscar camión"
                            :disabled="!canSearch"
                            :loading="tripsHttp.processing"
                            fluid
                            @click="search"
                        >
                            <template #icon>
                                <SearchIcon class="size-4 mr-2" />
                            </template>
                        </Button>
                    </div>
                </template>
            </Card>

            <!-- Results -->
            <section
                v-if="searched"
                class="space-y-3"
            >
                <h2 class="font-semibold">
                    {{ trips.length > 0 ? `${trips.length} opción(es)` : 'Sin resultados' }}
                </h2>

                <Message
                    v-if="trips.length === 0"
                    severity="warn"
                >
                    No encontramos un camión directo entre esas paradas. Por ahora solo mostramos rutas directas (sin transbordos).
                </Message>

                <div
                    v-if="firstTrip"
                    class="h-64 w-full rounded-lg bg-surface-100 dark:bg-surface-800"
                >
                    <ClientOnly>
                        <RouteMap
                            :stops="firstTrip.segmentStops"
                            :path="firstTripPath"
                            :color="firstTrip.route.color"
                            height-class="h-64"
                        />
                    </ClientOnly>
                </div>

                <Card
                    v-for="(trip, index) in trips"
                    :key="index"
                >
                    <template #content>
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-2 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-sm font-semibold text-white"
                                        :style="{ backgroundColor: trip.route.color }"
                                    >
                                        <Bus class="size-3.5" />
                                        {{ trip.route.code }}
                                    </span>
                                    <span class="font-medium truncate">{{ trip.route.name }}</span>
                                </div>

                                <div class="text-sm space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-muted-color w-16 shrink-0">Sube en</span>
                                        <span class="font-medium">{{ trip.boardStop.name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <ArrowDown class="size-4 text-muted-color" />
                                        <span class="text-xs text-muted-color">{{ trip.stopCount }} paradas · {{ directionLabel(trip.direction) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-muted-color w-16 shrink-0">Baja en</span>
                                        <span class="font-medium">{{ trip.alightStop.name }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 text-xs text-muted-color pt-1">
                                    <span>{{ vehicleTypeLabel(trip.route.vehicleType) }}</span>
                                    <span>·</span>
                                    <span>{{ formatFare(trip.route.fare) }}</span>
                                    <span v-if="trip.route.frequencyMinutes">·</span>
                                    <span v-if="trip.route.frequencyMinutes">cada {{ trip.route.frequencyMinutes }} min</span>
                                </div>
                            </div>

                            <Link :href="route('transit.routes.show', { route: trip.route.id })">
                                <Button
                                    label="Ver ruta"
                                    size="small"
                                    severity="secondary"
                                    outlined
                                />
                            </Link>
                        </div>
                    </template>
                </Card>
            </section>
        </div>
    </TransitLayout>
</template>
