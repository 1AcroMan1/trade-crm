<template>
    <div class="app-container">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-menu">
                    <router-link v-for="link in links"
                                 :key="link.path"
                                 :to="link.path"
                                 class="nav-link">
                        {{ link.name }}
                    </router-link>
                </div>
            </div>
        </nav>

        <main class="main-content">
            <router-view />
        </main>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                links: [
                    { name: 'Заказы', path: '/orders' },
                    { name: 'Товары', path: '/products' },
                    { name: 'Склады', path: '/warehouses' },
                    { name: 'Движения', path: '/movements' }
                ]
            }
        }
    }
</script>
