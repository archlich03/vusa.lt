<template>

  <Head>
    <title v-if="title">
      {{ $t(title) }}
    </title>
  </Head>
  <!-- Usually maybe for breadcrumb -->
  <div class="container">
    <div class="ml-4">
      <slot name="above-header" />
    </div>

    <header v-if="title" class="z-10 col-span-2 m-4 mt-8 flex max-w-6xl flex-row items-center gap-4 pr-8"
      :class="{ 'pb-2': title }">
      <NButton v-if="!isIndex && backUrl" style="margin-top: 0.1rem" quaternary size="small" @click="back">
        <template #icon>
          <IFluentChevronLeft24Filled />
        </template>
      </NButton>
      <h1 class="my-0 inline-flex items-center gap-3">
        <NIcon v-if="headingIcon" :component="headingIcon" />
        <slot name="title">
          {{ $t(title) }}
        </slot>
      </h1>
      <slot name="create-button">
        <Link v-if="isIndex && createUrl" :href="createUrl">
        <div class="flex">
          <NButton round size="tiny" :theme-overrides="{ border: '1.2px solid' }">
            <template #icon>
              <IFluentAdd24Filled />
            </template>{{ $t("forms.add") }}
          </NButton>
        </div>
        </Link>
      </slot>
      <aside class="ml-6">
        <NScrollbar x-scrollable>
          <div class="flex flex-row items-center justify-between gap-2">
            <slot name="after-heading" />
            <aside class="ml-auto">
              <slot name="aside-header" />
            </aside>
          </div>
        </NScrollbar>
      </aside>
    </header>
    <slot name="below-header" />
    <NDivider v-if="title && headerDivider" style="margin-top: 0px" class="mt-0" />

    <div class="ml-4 max-w-6xl" :class="{ 'col-span-2': !aside }">
      <FadeTransition appear>
        <div class="relative overflow-visible">
          <slot />
        </div>
      </FadeTransition>
    </div>
    <div v-if="aside" class="sticky top-4 px-2">
      <slot name="aside-card" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { trans as $t } from "laravel-vue-i18n";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

import FadeTransition from "@/Components/Transitions/FadeTransition.vue";

defineProps<{
  aside?: boolean;
  breadcrumb?: true;
  backUrl?: string;
  createUrl?: string;
  headerDivider?: boolean;
  headingIcon?: any;
  title?: string;
}>();

const isIndex = computed(() => {
  return route().current("*.index");
});

const back = () => window.history.back();
</script>
