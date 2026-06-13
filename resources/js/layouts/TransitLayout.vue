<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Bus, Map, Search } from '@lucide/vue'
import AppHead from '@/components/AppHead.vue'
import Container from '@/components/Container.vue'
import SelectColorModeButton from '@/components/SelectColorModeButton.vue'
import { route } from '@/utils/route'

const props = withDefaults(defineProps<{
    title: string,
    description?: string,
}>(), {
    description: 'Conoce qué camión te lleva a tu destino en Cancún: rutas, horarios y tarifas.',
})

const navLinks = [
    { label: 'Buscar', href: route('transit.home'), icon: Search },
    { label: 'Rutas', href: route('transit.routes'), icon: Map },
]
</script>

<template>
    <AppHead
        :title="props.title"
        :description="props.description"
    />

    <div class="min-h-screen flex flex-col">
        <nav class="dynamic-bg shadow-sm">
            <Container>
                <div class="flex items-center justify-between h-[var(--header-height,4rem)]">
                    <Link
                        :href="route('transit.home')"
                        class="flex items-center gap-2 font-semibold text-lg shrink-0"
                    >
                        <Bus class="size-6 text-primary" />
                        <span>busbox</span>
                    </Link>

                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                        >
                            <Button
                                :label="link.label"
                                text
                                severity="secondary"
                                size="small"
                            >
                                <template #icon>
                                    <component
                                        :is="link.icon"
                                        class="size-4"
                                    />
                                </template>
                            </Button>
                        </Link>
                        <SelectColorModeButton />
                    </div>
                </div>
            </Container>
        </nav>

        <main class="flex-1">
            <Container vertical>
                <slot />
            </Container>
        </main>
    </div>
</template>
