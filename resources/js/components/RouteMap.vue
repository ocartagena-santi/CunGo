<script setup lang="ts">
import 'leaflet/dist/leaflet.css'
import { computed } from 'vue'
import { LCircleMarker, LMap, LPolyline, LTileLayer, LTooltip } from '@vue-leaflet/vue-leaflet'

type Stop = App.Data.StopData
type LatLng = App.Data.LatLngData

const props = withDefaults(defineProps<{
    stops?: Stop[],
    path?: LatLng[],
    color?: string,
    heightClass?: string,
}>(), {
    stops: () => [],
    path: () => [],
    color: '#2563eb',
    heightClass: 'h-72 md:h-96',
})

// Default center: downtown Cancún, used when there is nothing to fit.
const CANCUN: [number, number] = [21.1619, -86.8515]

const pathLatLngs = computed<[number, number][]>(() => props.path.map((p) => [p.lat, p.lng]))

const allPoints = computed<[number, number][]>(() => {
    const stopPoints = props.stops.map((s) => [s.lat, s.lng] as [number, number])

    return [...pathLatLngs.value, ...stopPoints]
})

const bounds = computed<[[number, number], [number, number]] | undefined>(() => {
    const points = allPoints.value

    if (points.length === 0) {
        return undefined
    }

    const lats = points.map((p) => p[0])
    const lngs = points.map((p) => p[1])

    return [
        [Math.min(...lats), Math.min(...lngs)],
        [Math.max(...lats), Math.max(...lngs)],
    ]
})
</script>

<template>
    <div
        :class="heightClass"
        class="w-full overflow-hidden rounded-lg border border-surface-200 dark:border-surface-700"
    >
        <LMap
            :use-global-leaflet="false"
            :zoom="12"
            :center="CANCUN"
            :bounds="bounds"
            :options="{ scrollWheelZoom: false }"
            style="height: 100%; width: 100%;"
        >
            <LTileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                attribution="&copy; OpenStreetMap contributors"
                :max-zoom="19"
            />

            <LPolyline
                v-if="pathLatLngs.length > 1"
                :lat-lngs="pathLatLngs"
                :color="color"
                :weight="5"
                :opacity="0.85"
            />

            <LCircleMarker
                v-for="stop in stops"
                :key="stop.id"
                :lat-lng="[stop.lat, stop.lng]"
                :radius="stop.isLandmark ? 8 : 6"
                color="#ffffff"
                :weight="2"
                :fill-color="color"
                :fill-opacity="1"
            >
                <LTooltip>{{ stop.name }}</LTooltip>
            </LCircleMarker>
        </LMap>
    </div>
</template>
