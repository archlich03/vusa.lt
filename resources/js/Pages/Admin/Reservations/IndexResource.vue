<template>
  <IndexPageLayout :title="capitalize($tChoice('entities.resource.model', 2))" model-name="resources"
    :icon="Icons.RESOURCE" :can-use-routes :columns :paginated-models="resources" />
</template>

<script setup lang="tsx">
import { trans as $t, transChoice as $tChoice } from "laravel-vue-i18n";
import {
  type DataTableColumns,
  type DataTableSortState,
  NIcon,
  NImage,
  NImageGroup,
  NSpace,
} from "naive-ui";
import { computed, provide, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import { Icon } from "@iconify/vue";
import { capitalize } from "@/Utils/String";
import { tenantColumn } from "@/Composables/dataTableColumns";
import Icons from "@/Types/Icons/regular";
import IndexPageLayout from "@/Components/Layouts/IndexModel/IndexPageLayout.vue";

const props = defineProps<{
  resources: PaginatedModels<App.Entities.Resource>;
  categories: any;
}>();

const canUseRoutes = {
  create: true,
  show: false,
  edit: true,
  destroy: true,
};

const sorters = ref<Record<string, DataTableSortState["order"]>>({
  name: false,
});

provide("sorters", sorters);

const filters = ref<Record<string, any>>({
  'padalinys.id': [],
  'category.id': [],
  'is_reservable': [],
});

provide("filters", filters);

// add columns
const columns = computed<DataTableColumns<App.Entities.Resource>>(() => [
  {
    type: "expand",
    renderExpand(row) {
      return (
        <section class="flex flex-col gap-2 p-2">
          <NImageGroup>
            <NSpace>
              {row.media?.map((image) => (
                <NImage width="150" src={image.original_url} alt={image.name} />
              ))}
            </NSpace>
          </NImageGroup>
          <div>
            <strong>{$t("forms.fields.description")}</strong>
            <p>{row.description}</p>
          </div>
        </section>
      );
    },
  },
  {
    key: "image",
    render(row) {
      return row.media?.[0]?.original_url ?
        <NImage class="my-auto" showToolbar={false} width="50" height="50" objectFit="contain" src={row.media?.[0]?.original_url} alt={row.name} /> :
        <NIcon component={Icons.RESOURCE} />;
    },
    width: 55,
  },
  {
    title() {
      return $t("forms.fields.title");
    },
    key: "name",
    sorter: true,
    sortOrder: sorters.value.name,
    maxWidth: 300,
    ellipsis: {
      tooltip: true,
    },
  },
  {
    title: "Kategorija",
    key: "category.id",
    filter: true,
    defaultFilterOptionValues: filters.value["category.id"],
    filterOptionValues: filters.value["category_id"],
    filterOptions: props.categories.map((category) => {
      return {
        label: category.name,
        value: category.id,
      };
    }),
    render(row) {
      if (!row.category) {
        return;
      }
      return <div class="flex items-center gap-2"><Icon icon={`fluent:${row.category.icon}`} />{row.category.name}</div>;
    },
  },
  {
    title: "Ar skolinamas?",
    key: "is_reservable",
    filter: true,
    filterOptionValues: filters.value["is_reservable"],
    filterOptions: [
      { label: "Taip", value: true },
      { label: "Ne", value: false },
    ],
    render(row) {
      return row.is_reservable ? "✅ Taip" : "❌ Ne";
    }
  },
  {
    title() {
      return $t("forms.fields.quantity");
    },
    key: "capacity",
    width: 75,
  },
  {
    ...tenantColumn(filters, usePage().props.tenants),
    render(row) {
      return $t(row.tenant?.shortname);
    },
  },
]);
</script>
