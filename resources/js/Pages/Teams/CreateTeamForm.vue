<template>
    <f-section @submitted="createTeam">
        <template #title>
            Team Details
        </template>

        <template #description>
            Create a new team to collaborate with others on projects.
        </template>

        <template #form>
            <div class="col-span-6 p-3 bg-gray-100 rounded-md dark:bg-gray-800 sm:col-span-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Team Owner
                </h3>

                <div class="flex items-center mt-2">
                    <img
                        class="object-cover w-12 h-12 rounded-full"
                        :src="page.props.value.user.profile_photo_url"
                        :alt="$page.props.user.name"
                    />

                    <div class="ml-3 leading-tight">
                        <div class="text-base font-medium text-gray-600 dark:text-gray-400">{{ page.props.value.user.name }}</div>
                        <div class="text-sm text-gray-500">{{ page.props.value.user.email }}</div>
                    </div>
                </div>
            </div>

            <div class="col-span-6 space-y-1 sm:col-span-4">
                <f-input
                    label="Team Name"
                    autofocus
                    v-model="form.name"
                />

                <f-input-error :message="form.errors.name" />
            </div>
        </template>

        <template #actions>
            <x-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </x-button>
        </template>
    </f-section>
</template>

<script>
    import FInput from '@/Components/Form/Input'
    import FInputError from '@/Components/Form/InputError'
    import FSection from '@/Components/Form/Section'
    import XButton from '@/Components/Button'
    import { useForm, usePage } from '@inertiajs/inertia-vue3'

    export default {
        components: {
            FInput,
            FInputError,
            FSection,
            XButton,
        },
        setup() {
            const page = usePage()
            const form = useForm({ name: '' })
            const createTeam = () => {
                form.value.post(route('teams.store'), {
                    errorBag: 'createTeam',
                    preserveScroll: true,
                })
            }

            return {
                page,
                form,
                createTeam,
            }
        },
    }
</script>
