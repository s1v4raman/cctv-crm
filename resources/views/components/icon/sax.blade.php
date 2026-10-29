@props(['name', 'class' => 'w-5 h-5 shrink-0'])

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'xmlns' => 'http://www.w3.org/2000/svg']) }} aria-hidden="true">
@switch($name)
    @case('category')
    @case('dashboard')
        {{-- Iconsax: category-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M5 10h2c2 0 3-1 3-3V5c0-2-1-3-3-3H5C3 2 2 3 2 5v2c0 2 1 3 3 3zM17 10h2c2 0 3-1 3-3V5c0-2-1-3-3-3h-2c-2 0-3 1-3 3v2c0 2 1 3 3 3zM17 22h2c2 0 3-1 3-3v-2c0-2-1-3-3-3h-2c-2 0-3 1-3 3v2c0 2 1 3 3 3zM5 22h2c2 0 3-1 3-3v-2c0-2-1-3-3-3H5c-2 0-3 1-3 3v2c0 2 1 3 3 3z"/>
        @break

    @case('calendar')
    @case('calendar-2')
        {{-- Iconsax: calendar-2-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.995 13.7h.01M8.294 13.7h.01M8.294 16.7h.01"/>
        @break

    @case('user-add')
    @case('leads')
        {{-- Iconsax: user-add-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 12a5 5 0 100-10 5 5 0 000 10zM3.41 22c0-3.87 3.85-7 8.59-7 .96 0 1.89.13 2.76.37"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M22 18c0 .32-.04.63-.12.93-.09.4-.25.79-.46 1.13A3.97 3.97 0 0118 22a3.92 3.92 0 01-2.66-1.03c-.3-.26-.56-.57-.76-.91A3.92 3.92 0 0114 18a3.995 3.995 0 014-4c1.18 0 2.25.51 2.97 1.33.64.71 1.03 1.65 1.03 2.67zM19.49 17.98h-2.98M18 16.52v2.99"/>
        @break

    @case('clipboard-text')
    @case('surveys')
        {{-- Iconsax: clipboard-text-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M8 12.2h7M8 16.2h4.38M10 6h4c2 0 2-1 2-2 0-2-1-2-2-2h-4C9 2 8 2 8 4s1 2 2 2z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M16 4.02c3.33.18 5 1.41 5 5.98v6c0 4-1 6-6 6H9c-5 0-6-2-6-6v-6c0-4.56 1.67-5.8 5-5.98"/>
        @break

    @case('receipt-item')
    @case('quotations')
        {{-- Iconsax: receipt-item-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M22 6v2.42C22 10 21 11 19.42 11H16V4.01C16 2.9 16.91 2 18.02 2c1.09.01 2.09.45 2.81 1.17C21.55 3.9 22 4.9 22 6z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M2 7v14c0 .83.94 1.3 1.6.8l1.71-1.28c.4-.3.96-.26 1.32.1l1.66 1.67c.39.39 1.03.39 1.42 0l1.68-1.68c.35-.35.91-.39 1.3-.09l1.71 1.28c.66.49 1.6.02 1.6-.8V4c0-1.1.9-2 2-2H6C3 2 2 3.79 2 6v1z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13.01h3M9 9.01h3"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.996 13h.009M5.996 9h.009"/>
        @break

    @case('buildings')
    @case('buildings-2')
    @case('projects')
        {{-- Iconsax: buildings-2-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M6.7 18H4.15C2.72 18 2 17.28 2 15.85V4.15C2 2.72 2.72 2 4.15 2h4.3c1.43 0 2.15.72 2.15 2.15V6"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M17.37 8.42v11.16c0 1.61-.8 2.42-2.41 2.42H9.12c-1.61 0-2.42-.81-2.42-2.42V8.42C6.7 6.81 7.51 6 9.12 6h5.84c1.61 0 2.41.81 2.41 2.42z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M13.4 6V4.15c0-1.43.72-2.15 2.15-2.15h4.3C21.28 2 22 2.72 22 4.15v11.7c0 1.43-.72 2.15-2.15 2.15h-2.48M10 11h4M10 14h4M12 22v-3"/>
        @break

    @case('location')
    @case('sites')
        {{-- Iconsax: location-linear --}}
        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M12 13.43a3.12 3.12 0 100-6.24 3.12 3.12 0 000 6.24z"/>
        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3.62 8.49c1.97-8.66 14.8-8.65 16.76.01 1.15 5.08-2.01 9.38-4.78 12.04a5.193 5.193 0 01-7.21 0c-2.76-2.66-5.92-6.97-4.77-12.05z"/>
        @break

    @case('people')
    @case('workers')
        {{-- Iconsax: people-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 7.16a.605.605 0 00-.19 0 2.573 2.573 0 01-2.48-2.58c0-1.43 1.15-2.58 2.58-2.58a2.58 2.58 0 012.58 2.58A2.589 2.589 0 0118 7.16zM16.97 14.44c1.37.23 2.88-.01 3.94-.72 1.41-.94 1.41-2.48 0-3.42-1.07-.71-2.6-.95-3.97-.71M5.97 7.16c.06-.01.13-.01.19 0a2.573 2.573 0 002.48-2.58C8.64 3.15 7.49 2 6.06 2a2.58 2.58 0 00-2.58 2.58c.01 1.4 1.11 2.53 2.49 2.58zM7 14.44c-1.37.23-2.88-.01-3.94-.72-1.41-.94-1.41-2.48 0-3.42 1.07-.71 2.6-.95 3.97-.71M12 14.63a.605.605 0 00-.19 0 2.573 2.573 0 01-2.48-2.58c0-1.43 1.15-2.58 2.58-2.58a2.58 2.58 0 012.58 2.58c-.01 1.4-1.11 2.54-2.49 2.58zM9.09 17.78c-1.41.94-1.41 2.48 0 3.42 1.6 1.07 4.22 1.07 5.82 0 1.41-.94 1.41-2.48 0-3.42-1.59-1.06-4.22-1.06-5.82 0z"/>
        @break

    @case('clock')
    @case('attendance')
        {{-- Iconsax: clock-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9.66v2.79l1.4 1.4"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.5 12a6.5 6.5 0 1113 0c0 2.08-.98 3.94-2.5 5.13h-.01c-1.1.86-2.48 1.37-3.99 1.37-1.49 0-2.86-.5-3.96-1.35h-.01A6.486 6.486 0 015.5 12z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.03 17.15h.01c1.1.85 2.47 1.35 3.96 1.35 1.51 0 2.89-.51 3.99-1.37H16l-.51 2.47C15 21.5 13.9 22 12.55 22h-1.09c-1.35 0-2.46-.5-2.94-2.41l-.49-2.44zM8.03 6.85h.01C9.14 6 10.51 5.5 12 5.5c1.51 0 2.89.51 3.99 1.37H16l-.51-2.47C15 2.5 13.9 2 12.55 2h-1.09C10.11 2 9 2.5 8.52 4.41l-.49 2.44z"/>
        @break

    @case('wallet')
    @case('empty-wallet')
        {{-- Iconsax: empty-wallet-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M18 19H6c-3 0-4-1.5-4-4.5v-7C2 4.5 3 3 6 3h12c3 0 4 1.5 4 4.5v7c0 3-1 4.5-4 4.5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M18 6.5h-4.5c-1 0-1.8.8-1.8 1.8v1.4c0 1 .8 1.8 1.8 1.8H18c.6 0 1-.4 1-1V7.5c0-.6-.4-1-1-1zM2 10.5h15M6.5 15.5h4"/>
        @break

    @case('money')
    @case('wages')
        {{-- Iconsax: money-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M17 20.5H7c-3 0-5-1.5-5-5v-7c0-3.5 2-5 5-5h10c3 0 5 1.5 5 5v7c0 3.5-2 5-5 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6zM5.5 9.5v5M18.5 9.5v5"/>
        @break

    @case('briefcase')
        {{-- Iconsax: briefcase-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M8 22h8c4.02 0 4.74-1.61 4.95-3.57l.75-8C21.97 7.99 21.27 6 17 6H7c-4.27 0-4.97 1.99-4.7 4.43l.75 8C3.26 20.39 3.98 22 8 22zM8 6v-.8C8 3.43 8 2 11.2 2h1.6C16 2 16 3.43 16 5.2V6"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M14 13v1.02c0 1.09-.01 1.98-2 1.98-1.98 0-2-.88-2-1.97V13c0-1 0-1 1-1h2c1 0 1 0 1 1zM21.65 11A16.484 16.484 0 0114 14.02M2.62 11.27c2.25 1.54 4.79 2.47 7.38 2.76"/>
        @break

    @case('truck')
    @case('truck-time')
    @case('dcs')
        {{-- Iconsax: truck-time-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M17 17h1c2 0 3-1 3-3V9c0-1-.3-1.7-.8-2.2l-2-2C17.7 4.3 17 4 16 4h-2c-1 0-2 1-2 2v10c0 1 1 2 2 2h1M7 17H3c-1 0-2-1-2-2V8c0-2 1-3 3-3h8M8 12h4M7 8h5"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M19 12h-3c-.6 0-1-.4-1-1V8.5c0-.6.4-1 1-1h1.8c.4 0 .7.1 1 .4l1.8 1.8c.3.3.4.6.4 1V11c0 .6-.4 1-1 1z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M5.5 21a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM17.5 21a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/>
        @break

    @case('task')
    @case('task-square')
    @case('jobs')
        {{-- Iconsax: task-square-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M8.5 12l2.3 2.3 4.7-4.7"/>
        @break

    @case('video')
    @case('video-square')
    @case('cctv')
        {{-- Iconsax: video-square-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M12.9 8.65l-4.5 2.6c-1.2.69-1.2 1.81 0 2.5l4.5 2.6c1.2.69 2.18.12 2.18-1.25V9.9c0-1.37-.98-1.94-2.18-1.25z"/>
        @break

    @case('box')
    @case('box-1')
    @case('products')
        {{-- Iconsax: box-1-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M12 2l8.5 4.9v9.8L12 21.6 3.5 16.7V6.9L12 2zM12 11.8v9.8M12 11.8L3.5 6.9M12 11.8l8.5-4.9"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M7.75 4.45l8.5 4.9"/>
        @break

    @case('shopping-cart')
    @case('purchase-orders')
        {{-- Iconsax: shopping-cart-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M2 2h2.7l2.4 12c.2 1 1 1.7 2 1.7h9.6c1 0 1.9-.7 2.1-1.7l1.7-8.3c.2-.9-.5-1.7-1.4-1.7H6.2"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M16.5 19.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3zM8.5 19.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/>
        @break

    @case('shop')
    @case('suppliers')
        {{-- Iconsax: shop-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.9 14.5v5.5c0 1.1-.9 2-2 2H6.1c-1.1 0-2-.9-2-2v-5.5M18.31 12c2.02 0 3.5-1.64 3.3-3.65l-.28-2.75C20.97 3 19.97 2 17.35 2H14.3l.7 7.01c.17 1.65 1.66 2.99 3.31 2.99zM5.64 12c1.65 0 3.14-1.34 3.3-2.99l.22-2.21.48-4.8H6.59C3.97 2 2.97 3 2.61 5.6l-.27 2.75C2.14 10.36 3.62 12 5.64 12zM12 17c-1.67 0-2.5.83-2.5 2.5V22h5v-2.5c0-1.67-.83-2.5-2.5-2.5z"/>
        @break

    @case('personalcard')
    @case('employees')
        {{-- Iconsax: personalcard-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21H7c-4 0-5-1-5-5V8c0-4 1-5 5-5h10c4 0 5 1 5 5v8c0 4-1 5-5 5zM14 8h5M15 12h4M17 16h2"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.5 11.29a1.81 1.81 0 100-3.62 1.81 1.81 0 000 3.62zM12 16.33a3.02 3.02 0 00-2.74-2.72 7.72 7.72 0 00-1.52 0A3.03 3.03 0 005 16.33"/>
        @break

    @case('card')
    @case('finance')
        {{-- Iconsax: card-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M2 8.505h20M6 16.505h2M10.5 16.505h4"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.44 3.505h11.11c3.56 0 4.45.88 4.45 4.39v8.21c0 3.51-.89 4.39-4.44 4.39H6.44c-3.55.01-4.44-.87-4.44-4.38v-8.22c0-3.51.89-4.39 4.44-4.39z"/>
        @break

    @case('notification')
    @case('notification-bing')
    @case('alerts')
        {{-- Iconsax: notification-bing-linear --}}
        <path stroke-linecap="round" stroke-miterlimit="10" stroke-width="1.8" d="M12 6.44v3.33M12.02 2C8.34 2 5.36 4.98 5.36 8.66v2.1c0 .68-.28 1.7-.63 2.28l-1.27 2.12c-.78 1.31-.24 2.77 1.2 3.25a23.34 23.34 0 0014.73 0 2.22 2.22 0 001.2-3.25l-1.27-2.12c-.35-.58-.63-1.61-.63-2.28v-2.1C18.68 5 15.68 2 12.02 2z"/>
        <path stroke-miterlimit="10" stroke-width="1.8" d="M15.33 18.82c0 1.83-1.5 3.33-3.33 3.33-.91 0-1.75-.38-2.35-.98-.6-.6-.98-1.44-.98-2.35"/>
        @break

    @case('status-up')
    @case('analytics')
        {{-- Iconsax: status-up-linear --}}
        <path stroke-linecap="round" stroke-width="1.8" d="M6.88 18.15v-2.07M12 18.15v-4.14M17.12 18.15v-6.22M17.12 5.85l-.46.54a18.882 18.882 0 01-9.78 6.04"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.19 5.85h2.93v2.92"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7z"/>
        @break

    @case('security-user')
    @case('admin-users')
        {{-- Iconsax: security-user-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.49 2.23L5.5 4.1c-1.15.43-2.09 1.79-2.09 3.02v7.43c0 1.18.78 2.73 1.73 3.44l4.3 3.21c1.41 1.06 3.73 1.06 5.14 0l4.3-3.21c.95-.71 1.73-2.26 1.73-3.44V7.12c0-1.23-.94-2.59-2.09-3.02l-4.99-1.87c-.85-.31-2.21-.31-3.04 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10.92h-.13c-.94-.03-1.69-.81-1.69-1.76 0-.97.79-1.76 1.76-1.76s1.76.79 1.76 1.76c-.01.96-.76 1.73-1.7 1.76zM10.01 13.72c-.96.64-.96 1.69 0 2.33 1.09.73 2.88.73 3.97 0 .96-.64.96-1.69 0-2.33-1.08-.73-2.87-.73-3.97 0z"/>
        @break

    @case('setting')
    @case('setting-2')
        {{-- Iconsax: setting-2-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M2 12.88v-1.76c0-1.04.85-1.9 1.9-1.9 1.81 0 2.55-1.28 1.64-2.85-.52-.9-.21-2.07.7-2.59l1.73-.99c.79-.47 1.81-.19 2.28.6l.11.19c.9 1.57 2.38 1.57 3.29 0l.11-.19c.47-.79 1.49-1.07 2.28-.6l1.73.99c.91.52 1.22 1.69.7 2.59-.91 1.57-.17 2.85 1.64 2.85 1.04 0 1.9.85 1.9 1.9v1.76c0 1.04-.85 1.9-1.9 1.9-1.81 0-2.55 1.28-1.64 2.85.52.91.21 2.07-.7 2.59l-1.73.99c-.79.47-1.81.19-2.28-.6l-.11-.19c-.9-1.57-2.38-1.57-3.29 0l-.11.19c-.47.79-1.49 1.07-2.28.6l-1.73-.99a1.899 1.899 0 01-.7-2.59c.91-1.57.17-2.85-1.64-2.85-1.05 0-1.9-.86-1.9-1.9z"/>
        @break

    @case('call')
        {{-- Iconsax: call-linear --}}
        <path stroke-miterlimit="10" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M21.97 18.33c0 .36-.08.73-.25 1.09-.17.36-.39.7-.68 1.02-.49.54-1.03.93-1.64 1.18-.6.25-1.25.38-1.95.38-1.02 0-2.11-.24-3.26-.73s-2.3-1.15-3.44-1.98a28.75 28.75 0 01-3.28-2.8 28.414 28.414 0 01-2.79-3.27c-.82-1.14-1.48-2.28-1.96-3.41C2.24 8.67 2 7.58 2 6.54c0-.68.12-1.33.36-1.93.24-.61.62-1.17 1.15-1.67C4.15 2.31 4.85 2 5.59 2c.28 0 .56.06.81.18.26.12.49.3.67.56l2.32 3.27c.18.25.31.48.4.7.09.21.14.42.14.61 0 .24-.07.48-.21.71-.13.23-.32.47-.56.71l-.76.79c-.11.11-.16.24-.16.4 0 .08.01.15.03.23.03.08.06.14.08.2.18.33.49.76.93 1.28.45.52.93 1.05 1.45 1.58.54.53 1.06 1.02 1.59 1.47.52.44.95.74 1.29.92.05.02.11.05.18.08.08.03.16.04.25.04.17 0 .3-.06.41-.17l.76-.75c.25-.25.49-.44.72-.56.23-.14.46-.21.71-.21.19 0 .39.04.61.13.22.09.45.22.7.39l3.31 2.35c.26.18.44.39.55.64.1.25.16.5.16.78z"/>
        @break

    @case('sms')
        {{-- Iconsax: sms-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M17 20.5H7c-3 0-5-1.5-5-5v-7c0-3.5 2-5 5-5h10c3 0 5 1.5 5 5v7c0 3.5-2 5-5 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="1.8" d="M17 9l-3.13 2.5c-1.03.82-2.72.82-3.75 0L7 9"/>
        @break

    @case('logout')
        {{-- Iconsax: logout-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.9 7.56c.31-3.6 2.16-5.07 6.21-5.07h.13c4.47 0 6.26 1.79 6.26 6.26v6.52c0 4.47-1.79 6.26-6.26 6.26h-.13c-4.02 0-5.87-1.45-6.2-4.99M15 12H3.62M5.85 8.65L2.5 12l3.35 3.35"/>
        @break

    @case('moon')
        {{-- Iconsax: moon-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.03 12.42c.36 5.15 4.73 9.34 9.96 9.57 3.69.16 6.99-1.56 8.97-4.27.82-1.11.38-1.85-.99-1.6-.67.12-1.36.17-2.08.14C13 16.06 9 11.97 8.98 7.14c-.01-1.3.26-2.53.75-3.65.54-1.24-.11-1.83-1.36-1.3C4.41 3.86 1.7 7.85 2.03 12.42z"/>
        @break

    @case('sun')
        {{-- Iconsax: sun-1-linear --}}
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18.5a6.5 6.5 0 100-13 6.5 6.5 0 000 13z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.14 19.14l-.13-.13m0-14.02l.13-.13-.13.13zM4.86 19.14l.13-.13-.13.13zM12 2.08V2v.08zM12 22v-.08.08zM2.08 12H2h.08zM22 12h-.08.08zM4.99 4.99l-.13-.13.13.13z"/>
        @break

    @default
        {{-- Default fallback square --}}
        <rect x="3" y="3" width="18" height="18" rx="4" stroke-width="1.8"/>
@endswitch
</svg>
