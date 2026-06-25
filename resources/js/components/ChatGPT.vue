<template>
    <Popover class="chatgpt-popover" align="start" :inset="true">
        <template #trigger>
            <Button
                class="px-2!"
                variant="ghost"
                size="sm"
                :aria-label="button.text"
                v-tooltip="button.text"
            >
                <div class="flex items-center" v-html="button.html" />
            </Button>
        </template>

        <div class="p-3 min-w-[300px]">
            <p class="mb-2 text-sm">What would you like to generate?</p>

            <div class="mb-3 flex gap-4">
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="type" type="radio" value="full">
                    Full article
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="type" type="radio" value="paragraph">
                    Paragraph
                </label>
            </div>

            <label class="mb-3 block text-sm">
                <span v-if="type === 'full'">Enter an article title:</span>
                <span v-else>Enter a prompt for the paragraph:</span>
                <Input v-model="promptText" class="mt-1" />
            </label>

            <Button @click="send">Generate</Button>
        </div>
    </Popover>
</template>

<script>
import { Button, Input, Popover } from '@statamic/cms/ui';
import axios from 'axios';

export default {
    components: {
        Button,
        Input,
        Popover,
    },
    props: {
        button: Object,
        active: Boolean,
        variant: String,
        config: Object,
        bard: Object,
        editor: Object,
    },
    data() {
        return {
            promptText: '',
            type: 'full',
        };
    },
    methods: {
        async send() {
            this.editor.setEditable(false);

            const data = {
                type: this.type,
                promptText: this.promptText,
            };

            try {
                const response = await axios.post('/!/statamic-chatgpt', data);

                if (response?.data?.text) {
                    this.editor.commands.insertContent(response.data.text);
                    Statamic.$toast.success(__('Your content has been generated.'));
                } else {
                    Statamic.$toast.error(response?.data?.error || __('Something went wrong.'), { duration: 10000 });
                }
            } catch (error) {
                Statamic.$toast.error(error?.response?.data?.error || error.message || __('Something went wrong.'), { duration: 10000 });
            } finally {
                this.editor.commands.focus();
                this.editor.setEditable(true);
            }
        },
    },
};
</script>

<style>
@reference "../../css/addon.css";

.chatgpt-popover svg {
    width: 1rem;
    height: 1rem;
}

.ProseMirror[contenteditable="false"]::after {
    @apply absolute -mt-8 -ml-8 w-12 h-12 border-8 border-gray-400 rounded-full animate-spin inset-1/2 content-[''];
}

.ProseMirror[contenteditable="false"] {
    @apply bg-gray-200;
}
</style>
