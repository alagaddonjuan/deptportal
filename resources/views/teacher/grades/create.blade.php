<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Grades: Select Assessment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('teacher.grades.roster') }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="subject_id" :value="__('1. Select Your Subject')" />
                                <select id="subject_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="assessment_id" :value="__('2. Select Assessment')" />
                                <select name="assessment_id" id="assessment_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required disabled>
                                    <option value="">--</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Load Grade Roster') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('subject_id').addEventListener('change', async function() {
            const subjectId = this.value;
            const assessmentSelect = document.getElementById('assessment_id');
            assessmentSelect.innerHTML = '<option value="">Loading...</option>';
            assessmentSelect.disabled = true;

            if (!subjectId) {
                assessmentSelect.innerHTML = '<option value="">--</option>';
                return;
            }

            try {
                const response = await fetch(`/api/assignments?subject_id=${subjectId}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                assessmentSelect.innerHTML = '<option value="">Select an Assessment</option>';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(assessment => {
                        assessmentSelect.innerHTML += `<option value="${assessment.id}">${assessment.title}</option>`;
                    });
                    assessmentSelect.disabled = false;
                } else {
                    assessmentSelect.innerHTML = '<option value="">No assessments found</option>';
                }
            } catch (error) {
                console.error('Error fetching assessments:', error);
                assessmentSelect.innerHTML = '<option value="">Error loading</option>';
            }
        });
    </script>
</x-app-layout>