<script setup lang="ts">
import { ref } from 'vue'
import { useHttp } from '@inertiajs/vue3'
import { MapPin } from '@lucide/vue'
import { route } from '@/utils/route'

type Stop = App.Data.StopData

const model = defineModel<Stop | string | null>({ default: null })

withDefaults(defineProps<{
    placeholder?: string,
    inputId?: string,
}>(), {
    placeholder: 'Busca una parada o colonia…',
})

const suggestions = ref<Stop[]>([])
const http = useHttp<{ q: string }, Stop[]>({ q: '' })

async function search(event: { query: string }) {
    http.q = event.query
    suggestions.value = await http.get(route('api.stops.search'))
}
</script>

<template>
    <AutoComplete
        v-model="model"
        :suggestions="suggestions"
        option-label="name"
        :placeholder="placeholder"
        :input-id="inputId"
        :delay="250"
        :loading="http.processing"
        complete-on-focus
        fluid
        @complete="search"
    >
        <template #option="{ option }">
            <div class="flex items-start gap-2">
                <MapPin class="size-4 mt-0.5 shrink-0 text-primary" />
                <div class="flex flex-col">
                    <span>{{ option.name }}</span>
                    <span
                        v-if="option.colonia || option.landmark"
                        class="text-xs text-muted-color"
                    >
                        {{ option.landmark ?? option.colonia }}
                    </span>
                </div>
            </div>
        </template>
    </AutoComplete>
</template>
