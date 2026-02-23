<template>
  <div>
    <RoleTab v-if="tab.id === 'role'" class="my-5" />
    <!-- <UsersTab v-else-if="tab.id === 'users'" class="my-5" /> !-->

    <div v-else class="p-4">
      <h3 class="text-lg font-semibold mb-2">{{ tab.label }}</h3>
      <p class="text-gray-600 dark:text-gray-400">{{ tab.content }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import RoleTab from '@/components/settings/RoleTab.vue'
// import UsersTab from '@/components/settings/UsersTab.vue'

interface TabData {
  id: string
  label: string
  content: string
  count?: number
}

defineProps<{
  tab: TabData
}>()
</script>
