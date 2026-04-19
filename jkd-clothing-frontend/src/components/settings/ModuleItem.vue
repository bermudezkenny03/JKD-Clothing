<!-- ModuleItem.vue -->
<template>
  <div>
    <div class="overflow-hidden rounded-xl bg-gray-100 dark:bg-white/[0.03]">
      <!-- Header del módulo -->
      <div @click="toggleOpen" class="flex items-center justify-between px-6 py-4 cursor-pointer">
        <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">
          <i :class="module.icon" class="mr-2"></i>
          {{ module.name }}
        </h4>

        <div class="flex items-center space-x-2">
          <!-- Permission count badge -->
          <span class="px-2 py-1 text-xs font-medium bg-brand-500 text-white rounded-full">
            {{ totalSelectedPermissions }}/{{ totalAvailablePermissions }}
          </span>

          <!-- Toggle button -->
          <button class="text-gray-500 dark:text-gray-400">
            <span v-if="!isOpen">
              <svg class="fill-current" width="28" height="28" viewBox="0 0 28 28" fill="none">
                <path
                  d="M7 12.9998C6.44772 12.9998 6 13.4475 6 13.9998C6 14.5521 6.44772 14.9998 7 14.9998V12.9998ZM21.0008 14.9998C21.5531 14.9998 22.0008 14.5521 22.0008 13.9998C22.0008 13.4475 21.5531 12.9998 21.0008 12.9998V14.9998ZM15.0003 6.99951C15.0003 6.44723 14.5526 5.99951 14.0003 5.99951C13.448 5.99951 13.0003 6.44723 13.0003 6.99951H15.0003ZM13.0003 21.0003C13.0003 21.5526 13.448 22.0003 14.0003 22.0003C14.5526 22.0003 15.0003 21.5526 15.0003 21.0003H13.0003ZM7 14.9998H21.0008V12.9998H7V14.9998ZM13.0003 6.99951V21.0003H15.0003V6.99951H13.0003Z"
                  fill=""
                />
              </svg>
            </span>
            <span v-else>
              <svg class="fill-current" width="28" height="28" viewBox="0 0 28 28" fill="none">
                <path
                  d="M7 12.9995C6.44772 12.9995 6 13.4472 6 13.9995C6 14.5518 6.44772 14.9995 7 14.9995V12.9995ZM21.0008 14.9995C21.5531 14.9995 22.0008 14.5518 22.0008 13.9995C22.0008 13.4472 21.5531 12.9995 21.0008 12.9995V14.9995ZM7 14.9995H21.0008V12.9995H7V14.9995Z"
                  fill=""
                />
              </svg>
            </span>
          </button>
        </div>
      </div>

      <!-- Contenido del módulo - Solo submódulos -->
      <div v-show="isOpen" class="border-t border-brand-100 dark:border-brand-200">
        <!-- Submódulos -->
        <div v-if="childModules.length > 0">
          <div
            v-for="childModule in childModules"
            :key="childModule.id"
            class="p-6 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
          >
            <h6 class="text-sm font-semibold text-gray-600 dark:text-white mb-3 flex items-center">
              <i :class="childModule.icon" class="mr-2"></i>
              {{ childModule.name }}
              <span
                class="ml-2 px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 dark:text-white rounded-full"
              >
                {{ selectedChildPermissions[childModule.slug]?.length || 0 }}/{{
                  permissions.length
                }}
              </span>
            </h6>

            <!-- Select All para submódulo -->
            <div class="mb-3">
              <label
                class="flex items-center text-sm font-medium text-gray-700 cursor-pointer select-none dark:text-white"
              >
                <div class="relative">
                  <input
                    type="checkbox"
                    :checked="isAllChildSelected(childModule.slug)"
                    @change="toggleAllChild(childModule.slug)"
                    class="sr-only"
                  />
                  <div
                    :class="
                      isAllChildSelected(childModule.slug)
                        ? 'border-brand-500 bg-brand-500'
                        : 'bg-transparent border-gray-300 dark:border-gray-700'
                    "
                    class="mr-3 flex h-4 w-4 items-center justify-center rounded border-[1.25px] hover:border-brand-500"
                  >
                    <span :class="isAllChildSelected(childModule.slug) ? '' : 'opacity-0'">
                      <svg width="12" height="12" viewBox="0 0 14 14" fill="none">
                        <path
                          d="M11.6666 3.5L5.24992 9.91667L2.33325 7"
                          stroke="white"
                          stroke-width="1.94437"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </span>
                  </div>
                </div>
                Select All {{ childModule.name }} Permissions
              </label>
            </div>

            <!-- Permisos del submódulo -->
            <div class="grid grid-cols-2 gap-2">
              <label
                v-for="permission in permissions"
                :key="`child-${childModule.id}-${permission.id}`"
                class="flex items-center text-sm text-gray-600 cursor-pointer select-none dark:text-white"
              >
                <div class="relative">
                  <input
                    type="checkbox"
                    :checked="
                      selectedChildPermissions[childModule.slug]?.includes(permission.slug) || false
                    "
                    @change="toggleChildPermission(childModule.slug, permission.slug)"
                    class="sr-only"
                  />
                  <div
                    :class="
                      selectedChildPermissions[childModule.slug]?.includes(permission.slug)
                        ? 'border-brand-500 bg-brand-500'
                        : 'bg-transparent border-gray-300 dark:border-gray-700'
                    "
                    class="mr-2 flex h-4 w-4 items-center justify-center rounded border-[1.25px] hover:border-brand-500"
                  >
                    <span
                      :class="
                        selectedChildPermissions[childModule.slug]?.includes(permission.slug)
                          ? ''
                          : 'opacity-0'
                      "
                    >
                      <svg width="10" height="10" viewBox="0 0 14 14" fill="none">
                        <path
                          d="M11.6666 3.5L5.24992 9.91667L2.33325 7"
                          stroke="white"
                          stroke-width="1.94437"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                    </span>
                  </div>
                </div>
                {{ permission.name }}
              </label>
            </div>
          </div>
        </div>

        <!-- Mensaje cuando no hay submódulos -->
        <div v-else class="p-6 text-center text-gray-500 dark:text-gray-400">
          No submodules available for {{ module.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  module: {
    type: Object,
    required: true,
  },
  permissions: {
    type: Array<any>,
    required: true,
  },
  rolePermissions: {
    type: Object,
    default: () => ({ parent: [], children: {} }),
  },
  childModules: {
    type: Array<any>,
    default: () => [],
  },
  initialOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['permissions-changed'])

const isOpen = ref(props.initialOpen)
const selectedChildPermissions = ref<Record<string, string[]>>({})

// Computed - Solo cuenta los permisos de los submódulos
const totalSelectedPermissions = computed(() => {
  return Object.values(selectedChildPermissions.value).reduce((sum, perms) => sum + perms.length, 0)
})

const totalAvailablePermissions = computed(() => {
  return props.childModules.length * props.permissions.length
})

// Methods
const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

const isAllChildSelected = (childSlug: string) => {
  const childPerms = selectedChildPermissions.value[childSlug] || []
  return props.permissions.length > 0 && childPerms.length === props.permissions.length
}

const toggleChildPermission = (childSlug: string, permissionSlug: string) => {
  if (!selectedChildPermissions.value[childSlug]) {
    selectedChildPermissions.value[childSlug] = []
  }

  const index = selectedChildPermissions.value[childSlug].indexOf(permissionSlug)
  if (index > -1) {
    selectedChildPermissions.value[childSlug].splice(index, 1)
  } else {
    selectedChildPermissions.value[childSlug].push(permissionSlug)
  }
}

const toggleAllChild = (childSlug: string) => {
  if (isAllChildSelected(childSlug)) {
    selectedChildPermissions.value[childSlug] = []
  } else {
    selectedChildPermissions.value[childSlug] = props.permissions.map((p) => p.slug)
  }
}

// Watch para emitir cambios - Solo de submódulos
watch(
  selectedChildPermissions,
  () => {
    // Emitir cambios de cada submódulo
    Object.entries(selectedChildPermissions.value).forEach(([childSlug, permissions]) => {
      emit('permissions-changed', childSlug, permissions)
    })
  },
  { deep: true },
)

// Watch para cambios externos
watch(
  () => props.rolePermissions,
  (newRolePermissions) => {
    // Solo inicializar los permisos de los submódulos
    selectedChildPermissions.value = { ...(newRolePermissions.children || {}) }
  },
  { immediate: true, deep: true },
)
</script>
