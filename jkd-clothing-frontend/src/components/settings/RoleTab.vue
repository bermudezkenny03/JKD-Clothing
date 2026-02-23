<!-- RoleTab.vue -->
<template>
  <div class="space-y-6">
    <!-- Role Selector -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Select Role</h3>
      <div class="relative z-20 bg-transparent">
        <select
          v-model="selectedRoleId"
          @change="loadRolePermissions"
          class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
          :class="{ 'text-gray-800 dark:text-white/90': selectedRoleId }"
        >
          <option value="">-- Select a role --</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
        </select>
        <span
          class="absolute z-30 text-gray-700 -translate-y-1/2 pointer-events-none right-4 top-1/2 dark:text-gray-400"
        >
          <svg
            class="stroke-current"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396"
              stroke=""
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading || saving" class="flex justify-center py-8">
      <LoadingSpinner />
    </div>

    <!-- Modules and Permissions -->
    <div v-else-if="selectedRoleId" class="grid grid-cols-1 gap-x-8 xl:grid-cols-2">
      <div class="space-y-3">
        <ModuleItem
          v-for="(module, index) in leftColumnModules"
          :key="module.id"
          :module="module"
          :permissions="availablePermissions"
          :rolePermissions="getAllModulePermissions(module)"
          :childModules="getChildModules(module)"
          :initialOpen="index === 0"
          @permissions-changed="onPermissionsChanged"
        />
      </div>
      <div class="space-y-3">
        <ModuleItem
          v-for="module in rightColumnModules"
          :key="module.id"
          :module="module"
          :permissions="availablePermissions"
          :rolePermissions="getAllModulePermissions(module)"
          :childModules="getChildModules(module)"
          @permissions-changed="onPermissionsChanged"
        />
      </div>
    </div>

    <!-- Save Button -->
    <div v-if="selectedRoleId && hasChanges" class="flex justify-end">
      <button
        @click="savePermissions"
        :disabled="saving"
        class="px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ saving ? 'Saving...' : 'Save Permissions' }}
      </button>
    </div>

    <!-- Success Message -->
    <div v-if="showSuccessMessage" class="bg-green-50 border border-green-200 rounded-lg p-4">
      <div class="flex">
        <div class="ml-3">
          <p class="text-sm font-medium text-green-800">Permissions saved successfully!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, ref, computed } from 'vue'
import ModuleItem from './ModuleItem.vue'
import { permissionService } from '@/services/permissionService'
import LoadingSpinner from '../common/LoadingSpinner.vue'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const roles = ref<any[]>([])
const modules = ref<any[]>([])
const availablePermissions = ref<any[]>([])
const selectedRoleId = ref('')
const rolePermissions = ref<Record<string, string[]>>({})
const pendingChanges = ref<Record<string, string[]>>({})
const saving = ref(false)
const loading = ref(false)
const showSuccessMessage = ref(false)

const parentModules = computed(() => {
  return modules.value.filter((module) => !module.parent_id)
})

const leftColumnModules = computed(() => {
  const mid = Math.ceil(parentModules.value.length / 2)
  return parentModules.value.slice(0, mid)
})

const rightColumnModules = computed(() => {
  const mid = Math.ceil(parentModules.value.length / 2)
  return parentModules.value.slice(mid)
})

const hasChanges = computed(() => {
  return Object.keys(pendingChanges.value).length > 0
})

const loadGeneralData = async () => {
  loading.value = true
  try {
    const response = await permissionService.getGeneralData()
    roles.value = response.data.roles
    modules.value = response.data.modules
    availablePermissions.value = response.data.permissions

    console.log('Roles:', roles.value)
    console.log('Modules:', modules.value)
    selectedRoleId.value = roles.value[0].id
    await loadRolePermissions()
  } catch (error) {
    console.error('Error loading general data:', error)
  } finally {
    loading.value = false
  }
}

const loadRolePermissions = async () => {
  if (!selectedRoleId.value) {
    rolePermissions.value = {}
    pendingChanges.value = {}
    return
  }

  try {
    loading.value = true
    const response = await permissionService.getRolePermissions(selectedRoleId.value)
    rolePermissions.value = response.data.permissions || {}
    pendingChanges.value = {}

    console.log('Role permissions loaded:', rolePermissions.value)
  } catch (error) {
    console.error('Error loading role permissions:', error)
    rolePermissions.value = {}
  } finally {
    loading.value = false
  }
}

const getChildModules = (module: any) => {
  return module.children || []
}

const getAllModulePermissions = (module: any) => {
  const children = module.children || []

  return {
    parent: [], // Ya no necesitamos los permisos del padre
    children: children.reduce(
      (acc, child) => {
        acc[child.slug] = rolePermissions.value[child.slug] || []
        return acc
      },
      {} as Record<string, string[]>,
    ),
  }
}

const onPermissionsChanged = (moduleSlug: string, permissions: string[]) => {
  pendingChanges.value[moduleSlug] = permissions
}

const savePermissions = async () => {
  if (!selectedRoleId.value) return

  saving.value = true
  try {
    console.log('💾 Saving permissions...')
    console.log('📊 Pending changes:', pendingChanges.value)

    const completePermissions = { ...rolePermissions.value }

    Object.entries(pendingChanges.value).forEach(([moduleSlug, permissions]) => {
      completePermissions[moduleSlug] = permissions
    })

    const modulesToUpdate: any = []

    parentModules.value.forEach((parentModule) => {
      // Solo procesar submódulos, no el módulo padre
      const childModules = parentModule.children || []
      childModules.forEach((childModule: any) => {
        const childPermissions = completePermissions[childModule.slug] || []

        if (childPermissions.length > 0) {
          const permissionIds = childPermissions
            .map((slug) => availablePermissions.value.find((p) => p.slug === slug)?.id)
            .filter(Boolean)

          if (permissionIds.length > 0) {
            modulesToUpdate.push({
              module_id: childModule.id,
              permission_ids: permissionIds,
            })
          }
        }
      })
    })

    const response = await permissionService.assignPermissions(selectedRoleId.value, {
      modules: modulesToUpdate,
    })

    console.log('✅ Save response:', response)

    console.log('response.data.role_id', response.data.role_id)
    console.log('authStore.user?.role?.id', authStore.user?.role?.id)

    if (response.data.role_id == authStore.user?.role?.id && response.data.modules) {
      console.log('Same role... changing modules')
      authStore.modules = response.data.modules
    }

    await loadRolePermissions()

    pendingChanges.value = {}

    showSuccessMessage.value = true
    setTimeout(() => {
      showSuccessMessage.value = false
    }, 3000)
  } catch (error: any) {
    console.error('❌ Error saving permissions:', error)

    if (error.response?.data?.errors) {
      console.error('Validation errors:', error.response.data.errors)
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await loadGeneralData()
})
</script>
