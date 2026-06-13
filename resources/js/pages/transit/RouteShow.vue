<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, Bus, Clock, MapPin, Ticket } from '@lucide/vue'
import TransitLayout from '@/layouts/TransitLayout.vue'
import { route as routeHelper } from '@/utils/route'
import { formatFare, vehicleTypeLabel } from '@/utils/transit'

const props = defineProps<{
    route: App.Data.RouteDetailData,
}>()

type Direction = App.Enums.RouteDirection

const direction = ref<Direction>('ida')

const directionOptions = [
    { label: 'Ida', value: 'ida' as Direction },
    { label: 'Vuelta', value: 'vuelta' as Direction },
]

const stops = computed(() => (
    direction.value === 'ida' ? props.route.stopsIda : props.route.stopsVuelta
))
</script>

<template>
    <TransitLayout :title="`${route.route.code} · ${route.route.name}`">
        <div class="max-w-2xl mx-auto space-y-6">
            <Link
                :href="routeHelper('transit.routes')"
                class="inline-flex items-center gap-1 text-sm text-muted-color"
            >
                <ArrowLeft class="size-4" />
                Todas las rutas
            </Link>

            <header class="space-y-3">
                <div class="flex items-center gap-3">
                    <span
                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 font-semibold text-white"
                        :style="{ backgroundColor: route.route.color }"
                    >
                        <Bus class="size-4" />
                        {{ route.route.code }}
                    </span>
                    <h1 class="text-xl font-bold">
                        {{ route.route.name }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm text-muted-color">
                    <span class="inline-flex items-center gap-1">
                        <Bus class="size-4" /> {{ vehicleTypeLabel(route.route.vehicleType) }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <Ticket class="size-4" /> {{ formatFare(route.route.fare) }}
                    </span>
                    <span
                        v-if="route.route.frequencyMinutes"
                        class="inline-flex items-center gap-1"
                    >
                        <Clock class="size-4" /> cada {{ route.route.frequencyMinutes }} min
                    </span>
                    <span
                        v-if="route.route.operatingHours"
                        class="inline-flex items-center gap-1"
                    >
                        <Clock class="size-4" /> {{ route.route.operatingHours }}
                    </span>
                </div>
            </header>

            <SelectButton
                v-model="direction"
                :options="directionOptions"
                option-label="label"
                option-value="value"
                :allow-empty="false"
                aria-labelledby="Dirección"
            />

            <Card>
                <template #content>
                    <ol class="relative">
                        <li
                            v-for="(stop, index) in stops"
                            :key="`${stop.id}-${index}`"
                            class="flex gap-3 pb-5 last:pb-0"
                        >
                            <div class="flex flex-col items-center">
                                <span class="flex items-center justify-center size-7 rounded-full bg-primary text-primary-contrast text-xs font-semibold shrink-0">
                                    {{ index + 1 }}
                                </span>
                                <span
                                    v-if="index < stops.length - 1"
                                    class="w-px flex-1 bg-surface-300 dark:bg-surface-600 mt-1"
                                />
                            </div>
                            <div class="pt-0.5 min-w-0">
                                <p class="font-medium flex items-center gap-1">
                                    {{ stop.name }}
                                    <MapPin
                                        v-if="stop.isLandmark"
                                        class="size-3.5 text-primary"
                                    />
                                </p>
                                <p
                                    v-if="stop.landmark || stop.colonia"
                                    class="text-xs text-muted-color"
                                >
                                    {{ stop.landmark ?? stop.colonia }}
                                </p>
                            </div>
                        </li>
                    </ol>
                </template>
            </Card>
        </div>
    </TransitLayout>
</template>
