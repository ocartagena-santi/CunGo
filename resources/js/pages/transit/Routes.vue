<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Bus, ChevronRight } from '@lucide/vue'
import TransitLayout from '@/layouts/TransitLayout.vue'
import { route } from '@/utils/route'
import { formatFare, vehicleTypeLabel } from '@/utils/transit'

defineProps<{
    routes: App.Data.RouteData[],
}>()
</script>

<template>
    <TransitLayout title="Rutas">
        <div class="max-w-2xl mx-auto space-y-6">
            <header class="space-y-1">
                <h1 class="text-2xl font-bold">
                    Rutas disponibles
                </h1>
                <p class="text-muted-color">
                    Todas las rutas que conoces en busbox.
                </p>
            </header>

            <div class="space-y-3">
                <Link
                    v-for="r in routes"
                    :key="r.id"
                    :href="route('transit.routes.show', { route: r.id })"
                    class="block"
                >
                    <Card pt:body:class="!p-4">
                        <template #content>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-sm font-semibold text-white shrink-0"
                                        :style="{ backgroundColor: r.color }"
                                    >
                                        <Bus class="size-4" />
                                        {{ r.code }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">
                                            {{ r.name }}
                                        </p>
                                        <p class="text-xs text-muted-color">
                                            {{ vehicleTypeLabel(r.vehicleType) }} · {{ formatFare(r.fare) }}
                                            <template v-if="r.frequencyMinutes">
                                                · cada {{ r.frequencyMinutes }} min
                                            </template>
                                        </p>
                                    </div>
                                </div>
                                <ChevronRight class="size-5 text-muted-color shrink-0" />
                            </div>
                        </template>
                    </Card>
                </Link>
            </div>
        </div>
    </TransitLayout>
</template>
