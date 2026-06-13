<script setup lang="ts">
import { defineAsyncComponent } from 'vue'
import TransitLayout from '@/layouts/TransitLayout.vue'
import ClientOnly from '@/components/ClientOnly.vue'

const ExploreMap = defineAsyncComponent(() => import('@/components/ExploreMap.vue'))

defineProps<{
    stops: App.Data.StopData[],
    routes: App.Data.RoutePolylineData[],
}>()
</script>

<template>
    <TransitLayout title="Explorar mapa">
        <div class="max-w-4xl mx-auto space-y-4">
            <header class="space-y-1">
                <h1 class="text-2xl font-bold">
                    Explorar el mapa
                </h1>
                <p class="text-muted-color">
                    {{ stops.length }} paradas y {{ routes.length }} rutas en Cancún.
                </p>
            </header>

            <div class="h-[70vh] w-full rounded-lg bg-surface-100 dark:bg-surface-800">
                <ClientOnly>
                    <ExploreMap
                        :stops="stops"
                        :routes="routes"
                    />
                </ClientOnly>
            </div>
        </div>
    </TransitLayout>
</template>
