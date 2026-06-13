<script setup lang="ts">
import 'leaflet/dist/leaflet.css'
import { computed } from 'vue'
import { LCircleMarker, LMap, LPolyline, LTileLayer, LTooltip } from '@vue-leaflet/vue-leaflet'

type Stop = App.Data.StopData
type RoutePolyline = App.Data.RoutePolylineData

const props = withDefaults(defineProps<{
    stops?: Stop[],
    routes?: RoutePolyline[],
    heightClass?: string,
}>(), {
    stops: () => [],
    routes: () => [],
    heightClass: 'h-[70vh]',
})

const CANCUN: [number, number] = [21.1619, -86.8515]

const bounds = computed<[[number, number], [number, number]] | undefined>(() => {
    if (props.stops.length === 0) {
        return undefined
    }

    const lats = props.stops.map((s) => s.lat)
    const lngs = props.stops.map((s) => s.lng)

    return [
        [Math.min(...lats), Math.min(...lngs)],
        [Math.max(...lats), Math.max(...lngs)],
    ]
})

function toLatLngs(route: RoutePolyline): [number, number][] {
    return route.path.map((p) => [p.lat, p.lng])
}
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
            style="height: 100%; width: 100%;"
        >
            <LTileLayer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                attribution="&copy; OpenStreetMap contributors"
                :max-zoom="19"
            />

            <LPolyline
                v-for="r in routes"
                :key="r.id"
                :lat-lngs="toLatLngs(r)"
                :color="r.color"
                :weight="4"
                :opacity="0.8"
            >
                <LTooltip>{{ r.code }} · {{ r.name }}</LTooltip>
            </LPolyline>

            <LCircleMarker
                v-for="stop in stops"
                :key="stop.id"
                :lat-lng="[stop.lat, stop.lng]"
                :radius="stop.isLandmark ? 7 : 5"
                color="#ffffff"
                :weight="2"
                fill-color="#0f172a"
                :fill-opacity="0.9"
            >
                <LTooltip>{{ stop.name }}</LTooltip>
            </LCircleMarker>
        </LMap>
    </div>
</template>
