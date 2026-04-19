<template>
  <AdminLayout>
    <PageBreadcrumb :pageTitle="currentPageTitle" />
    <div class="space-y-5 sm:space-y-6">
      <ComponentCard title="Settings">
        <Tabs />
      </ComponentCard>
    </div>
  </AdminLayout>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import ComponentCard from '@/components/common/ComponentCard.vue'
import Tabs from '@/components/settings/Tabs.vue'

const currentPageTitle = ref('Settings')
</script>
