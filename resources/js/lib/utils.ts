import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(urlToCheck: NonNullable<InertiaLinkProps['href']>, currentUrl: string) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

const basePath = (import.meta.env.VITE_APP_BASE_PATH ?? '').replace(/^\/*/, '/').replace(/\/+$/, '');

/** Prefix a root-relative path with the app's base path (APP_BASE_PATH). */
export function appUrl(path: string) {
    return basePath + path;
}
