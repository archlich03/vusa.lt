<template>
  <CardModal :show="showModal" class="max-w-5xl" display-directive="show" :title="`${$t('Pranešti apie posėdį')}`"
    @close="$emit('close')">
    <!-- <template #header-extra>
        <NButton v-if="current === 1" text
          ><template #icon
            ><NIcon size="20" :component="PuzzlePiece20Regular" /></template></NButton>
</template> -->
    <div class="flex flex-col gap-8 md:flex-row">
      <NSteps vertical class="size-fit border-zinc-300 p-4 dark:border-zinc-500 md:border-r" size="small"
        :current="(current as number)" :status="currentStatus">
        <NStep :title="`1. ${$t('Pasirink instituciją')}`">
          <template #icon>
            <NIcon :component="IconsRegular.INSTITUTION" />
          </template>
        </NStep>
        <NStep :title="`2. ${$t('Nurodyk posėdžio datą')}`">
          <template #icon>
            <NIcon :component="IconsRegular.MEETING" />
          </template>
        </NStep>
        <NStep :title="`3. ${$t('Įrašyk darbotvarkės klausimus')}`">
          <template #icon>
            <NIcon :component="IconsRegular.AGENDA_ITEM" />
          </template>
        </NStep>
      </NSteps>
      <Suspense>
        <FadeTransition mode="out-in">
          <InstitutionSelectorForm v-if="current === 1" class="flex w-full flex-col items-start justify-center"
            @submit="handleInstitutionSelect" />
          <MeetingForm v-else-if="current === 2" class="flex w-full flex-col items-start justify-center"
            :meeting="meetingTemplate" @submit="handleMeetingFormSubmit" />
          <AgendaItemsForm v-else-if="current === 3" :loading @submit="handleAgendaItemsFormSubmit" />
        </FadeTransition>
      </Suspense>
    </div>
    <FadeTransition>
      <ModalHelperButton v-if="!showAlert && current === 3" @click="showAlert = true" />
    </FadeTransition>
  </CardModal>
</template>

<script setup lang="ts">
import { trans as $t } from "laravel-vue-i18n";
import { NIcon, NStep, NSteps } from "naive-ui";
import { ref } from "vue";
import { useStorage } from "@vueuse/core";

import { meetingTemplate } from "@/Types/formTemplates";
import { router, useForm } from "@inertiajs/vue3";
import AgendaItemsForm from "@/Components/AdminForms/Special/AgendaItemsForm.vue";
import CardModal from "@/Components/Modals/CardModal.vue";
import FadeTransition from "@/Components/Transitions/FadeTransition.vue";
import IconsRegular from "@/Types/Icons/regular";
import InstitutionSelectorForm from "../AdminForms/Special/InstitutionSelectorForm.vue";
import MeetingForm from "@/Components/AdminForms/MeetingForm.vue";
import ModalHelperButton from "../Buttons/ModalHelperButton.vue";

const emit = defineEmits(["close"]);

const props = defineProps<{
  institution?: App.Entities.Institution;
  showModal: boolean;
}>();

const loading = ref(false);
const institution_id = ref(props.institution?.id);
const current = ref(institution_id.value ? 2 : 1);
const currentStatus = ref<"process">("process");
const showAlert = useStorage("new-meeting-button-alert", true);

const meetingAgendaForm = useForm({
  meeting: {},
  // TODO: Shouldn't be an array
  agendaItems: [],
});

const handleInstitutionSelect = (id: string) => {
  institution_id.value = id;
  current.value += 1;
};

const handleMeetingFormSubmit = (meeting: Record<string, any>) => {
  meetingAgendaForm.meeting = meeting;
  current.value += 1;
};

const handleAgendaItemsFormSubmit = (agendaItems: Record<string, any>) => {
  loading.value = true;
  meetingAgendaForm.agendaItems = agendaItems;

  // submit meeting
  meetingAgendaForm
    .transform((data) => ({
      ...data.meeting,
      // add institution_id
      institution_id: institution_id.value,
    }))
    .post(route("meetings.store"), {
      // after success, submit agenda items
      onSuccess: (page) => {
        let id = page.props?.flash?.data.id;

        if (id === undefined) {
          return;
        }

        meetingAgendaForm
          .transform((data) => ({
            meeting_id: id,
            ...data.agendaItems,
          }))
          .post(route("agendaItems.store"), {
            // after success, redirect to meeting
            onSuccess: () => {
              emit("close");
              current.value = 1;
              meetingAgendaForm.reset();
            },
            onFinish: () => {
              loading.value = false;
              router.visit(route("meetings.show", id));
            },
          });
      },
      preserveScroll: true,
    });
};
</script>
