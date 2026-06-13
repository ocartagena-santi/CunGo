export const vehicleTypeLabels: Record<App.Enums.VehicleType, string> = {
    urban: 'Urban',
    camion: 'Camión',
    combi: 'Combi',
}

export const directionLabels: Record<App.Enums.RouteDirection, string> = {
    ida: 'Ida',
    vuelta: 'Vuelta',
}

export function vehicleTypeLabel(type: App.Enums.VehicleType): string {
    return vehicleTypeLabels[type] ?? type
}

export function directionLabel(direction: App.Enums.RouteDirection): string {
    return directionLabels[direction] ?? direction
}

export function formatFare(fare: string): string {
    const value = Number(fare)

    return Number.isNaN(value) ? fare : `$${value.toFixed(2)}`
}
