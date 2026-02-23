<template>
  <aside :class="[
    'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200',
    {
      'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
      'lg:w-[90px]': !isExpanded && !isHovered,
      'translate-x-0 w-[290px]': isMobileOpen,
      '-translate-x-full': !isMobileOpen,
      'lg:translate-x-0': true,
    },
  ]" @mouseenter="!isExpanded && (isHovered = true)" @mouseleave="isHovered = false">
    <div :class="['py-8 flex', !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-center']">
      <router-link to="/">
        <img v-if="isExpanded || isHovered || isMobileOpen" class="dark:hidden" src="/images/logo/logo.svg" alt="Logo"
          width="150" height="40" />
        <img v-if="isExpanded || isHovered || isMobileOpen" class="hidden dark:block" src="/images/logo/logo-dark.svg"
          alt="Logo" width="150" height="40" />
        <img v-else src="/images/logo/logo-icon.svg" alt="Logo" width="32" height="32" />
      </router-link>
    </div>
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
      <nav class="mb-6">
        <div class="flex flex-col gap-4">
          <div v-for="(menuGroup, groupIndex) in dynamicMenuGroups" :key="groupIndex">
            <h2 :class="[
              'mb-4 text-xs uppercase flex leading-[20px] text-gray-400',
              !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start',
            ]">
              <template v-if="isExpanded || isHovered || isMobileOpen">
                {{ menuGroup.title }}
              </template>
              <MoreDots v-else />
            </h2>
            <ul class="flex flex-col gap-4">
              <li v-for="(item, index) in menuGroup.items" :key="item.name">
                <button v-if="item.subItems" @click="toggleSubmenu(groupIndex, index)" :class="[
                  'menu-item group w-full',
                  {
                    'menu-item-active': isSubmenuOpen(groupIndex, index),
                    'menu-item-inactive': !isSubmenuOpen(groupIndex, index),
                  },
                  !isExpanded && !isHovered ? 'lg:justify-center' : 'lg:justify-start',
                ]">
                  <span :class="[
                    isSubmenuOpen(groupIndex, index)
                      ? 'menu-item-icon-active'
                      : 'menu-item-icon-inactive',
                  ]">
                    <component :is="item.icon" />
                  </span>
                  <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">{{
                    item.name
                    }}</span>
                  <ChevronDownIcon v-if="isExpanded || isHovered || isMobileOpen" :class="[
                    'ml-auto w-5 h-5 transition-transform duration-200',
                    { 'rotate-180 text-brand-500': isSubmenuOpen(groupIndex, index) },
                  ]" />
                </button>
                <router-link v-else-if="item.path" :to="item.path" :class="[
                  'menu-item group',
                  {
                    'menu-item-active': isActive(item.path),
                    'menu-item-inactive': !isActive(item.path),
                  },
                ]">
                  <span :class="[
                    isActive(item.path) ? 'menu-item-icon-active' : 'menu-item-icon-inactive',
                  ]">
                    <component :is="item.icon" />
                  </span>
                  <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">{{
                    item.name
                    }}</span>
                </router-link>
                <transition @enter="startTransition" @after-enter="endTransition" @before-leave="startTransition"
                  @after-leave="endTransition">
                  <div v-show="isSubmenuOpen(groupIndex, index) && (isExpanded || isHovered || isMobileOpen)
                    ">
                    <ul class="mt-2 space-y-1 ml-9">
                      <li v-for="subItem in item.subItems" :key="subItem.name">
                        <router-link :to="subItem.path" :class="[
                          'menu-dropdown-item',
                          {
                            'menu-dropdown-item-active': isActive(subItem.path),
                            'menu-dropdown-item-inactive': !isActive(subItem.path),
                          },
                        ]">
                          {{ subItem.name }}
                        </router-link>
                      </li>
                    </ul>
                  </div>
                </transition>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

import {
  GridIcon,
  ChevronDownIcon,
  MoreDots,
  LayoutDashboardIcon,
  BoxCubeIcon,
  UserGroupIcon,
  TaskIcon,
  BoxIcon,
} from '../../icons'
import { useSidebar } from '@/composables/useSidebar'

const route = useRoute()
const authStore = useAuthStore()

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar()

// Solo iconos para módulos padre (los submódulos no tienen iconos)
const moduleIcons: Record<string, any> = {
  // Solo módulos padre
  'dashboard': LayoutDashboardIcon,
  'catalog-management': BoxCubeIcon,      // Productos
  'order-management': TaskIcon,           // Órdenes
  'inventory-management': BoxIcon,        // Inventario
  'access-management': UserGroupIcon,     // Usuarios / Roles
}

const dynamicMenuGroups = computed(() => {
  if (!authStore.isAuthenticated || !authStore.modules || authStore.modules.length === 0) {
    return []
  }

  const menuItems: any[] = []

  authStore.modules.forEach((module) => {
    const hasChildren = module.children && module.children.length > 0

    if (hasChildren) {
      const visibleChildren = module.children!.filter((child) => child.show_in_sidebar == true)

      if (visibleChildren.length > 0) {
        const subItems = visibleChildren.map((child) => ({
          name: child.name,
          path: child.route,
        }))

        menuItems.push({
          icon: moduleIcons[module.slug] || GridIcon,
          name: module.name,
          subItems: subItems,
        })
      }
    } else {
      menuItems.push({
        icon: moduleIcons[module.slug] || GridIcon,
        name: module.name,
        path: module.route,
      })
    }
  })

  return [
    {
      title: 'Menu',
      items: menuItems,
    },
  ]
})

const isActive = (path: string): boolean => {
  if (route.path === path) return true
  if (path !== '/' && route.path.startsWith(path + '/')) return true
  return false
}

const toggleSubmenu = (groupIndex: number, itemIndex: number): void => {
  const key = `${groupIndex}-${itemIndex}`
  openSubmenu.value = openSubmenu.value === key ? null : key
}

const isSubmenuOpen = (groupIndex: number, itemIndex: number): boolean => {
  const key = `${groupIndex}-${itemIndex}`

  if (!dynamicMenuGroups.value[groupIndex]) return false

  const currentItem = dynamicMenuGroups.value[groupIndex].items[itemIndex]

  if (openSubmenu.value === key) return true
  if (currentItem.path && isActive(currentItem.path)) return true

  const anyDirectItemActive = dynamicMenuGroups.value.some((group) =>
    group.items.some((item: any) => item.path && isActive(item.path)),
  )

  if (anyDirectItemActive) return false

  if (currentItem.subItems) {
    return currentItem.subItems.some((subItem: any) => isActive(subItem.path))
  }

  return false
}

const startTransition = (el: HTMLElement): void => {
  el.style.height = 'auto'
  const height = el.scrollHeight
  el.style.height = '0px'
  el.style.height = height + 'px'
}

const endTransition = (el: HTMLElement): void => {
  el.style.height = ''
}
</script>
