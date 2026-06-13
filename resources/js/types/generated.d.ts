declare namespace App {
namespace Data {
export type DirectTripData = {
route: App.Data.RouteData,
direction: App.Enums.RouteDirection,
boardStop: App.Data.StopData,
alightStop: App.Data.StopData,
segmentStops: App.Data.StopData[],
stopCount: number,
};
export type ErrorToastResponseData = {
status: number,
errorSummary: string,
errorDetail: string,
};
export type LatLngData = {
lat: number,
lng: number,
};
export type RouteData = {
id: number,
code: string,
name: string,
color: string,
vehicleType: App.Enums.VehicleType,
fare: string,
frequencyMinutes: number | null,
operatingHours: string | null,
description: string | null,
isActive: boolean,
};
export type RouteDetailData = {
route: App.Data.RouteData,
stopsIda: App.Data.StopData[],
stopsVuelta: App.Data.StopData[],
pathIda: App.Data.LatLngData[],
pathVuelta: App.Data.LatLngData[],
};
export type StopData = {
id: number,
name: string,
lat: number,
lng: number,
landmark: string | null,
colonia: string | null,
isLandmark: boolean,
};
export type UserData = {
id: number,
name: string,
email: string,
role: App.Enums.UserRole,
emailVerifiedAt: string | null,
createdAt: string,
updatedAt: string,
};
}
namespace Enums {
export type RouteDirection = 'ida' | 'vuelta';
export type UserRole = 'user' | 'admin' | 'operator';
export type VehicleType = 'urban' | 'camion' | 'combi';
}
}
