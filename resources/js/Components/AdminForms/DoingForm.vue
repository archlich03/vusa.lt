<template>
  <NForm ref="formRef" :rules="rules" :model="doingForm">
    <NGrid cols="2">
      <NFormItemGi label="Veiklos pavadinimas" path="title" required :span="2">
        <NSelect
          v-model:value="doingForm.title"
          placeholder="Susitikimas su studentais"
          filterable
          tag
          :options="doingOptions"
          ><template #action>
            <span
              class="typography text-xs text-zinc-400"
              >Gali įrašyti ir savo veiklą...</span
            >
          </template></NSelect
        >
      </NFormItemGi>
      <NFormItemGi label="Data" :span="2" path="date" required>
        <NDatePicker
          v-model:formatted-value="doingForm.date"
          value-format="yyyy-MM-dd HH:mm:ss"
          :first-day-of-week="0"
          :format="'yyyy-MM-dd HH:mm:ss'"
          type="datetime"
          placeholder="Kada vyksta veikla?"
          clearable
          :actions="['confirm']"
        />
      </NFormItemGi>
      <NFormItemGi label="Atsakingas (-a)" :span="2">
        <UserPopover :user="$page.props.auth?.user" show-name />
      </NFormItemGi>

      <NFormItemGi :span="2" :show-label="false"
        ><NButton type="primary" @click="handleSubmit"
          >Sukurti</NButton
        ></NFormItemGi
      >
    </NGrid>
  </NForm>
</template>

<script setup lang="ts">
import {
  NButton,
  NDatePicker,
  NForm,
  NFormItemGi,
  NGrid,
  NSelect,
} from "naive-ui";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

import { modelDefaults } from "@/Types/formOptions";
import UserPopover from "../Avatars/UserPopover.vue";

const emit = defineEmits<{
  (e: "submit", form: any): void;
}>();

const props = defineProps<{
  doing: App.Entities.Doing;
}>();

const loading = ref(false);
const doingForm = useForm(props.doing);
const formRef = ref(null);

const rules = {
  title: {
    required: true,
    trigger: ["blur-sm"],
  },
  date: {
    required: true,
    trigger: ["blur-sm"],
    message: "Veiklos data yra privaloma",
  },
};

const doingOptions = modelDefaults.doing.map((doing) => ({
  label: doing,
  value: doing,
}));

const handleSubmit = () => {
  loading.value = true;
  emit("submit", doingForm);
};
</script>
