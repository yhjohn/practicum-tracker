<div class="mx-auto max-w-4xl px-4 py-10">
    @if ($step === 'submitted')
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-semibold text-gray-900">This consent form has already been signed</h1>
            <p class="mt-2 text-sm text-gray-600">Thank you, we've already received your signed consent. Please contact your counsellor if you have questions.</p>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <header class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-900">Informed Consent Form</h1>
                <p class="mt-2 text-sm text-gray-600">Open University Malaysia &middot; Master of Counselling</p>
            </header>

            <div class="space-y-4 text-sm leading-relaxed text-gray-700">
                <p>Welcome to this counselling therapy session and thank you for sharing information with me to help us to start your mental health journey together.</p>
                <p>My name is <strong>Joleen Poh Ee Bing</strong> and I am currently a student in Master of Counselling in Open University Malaysia. This session will be conducted on a <strong>pro-bono basis</strong>, meaning no charges will be applied to the client.</p>

                <h2 class="bg-gray-100 px-2 py-1 text-xs font-bold uppercase text-gray-700">Client Overview &amp; Rights</h2>
                <p>Counseling is a professional relationship that provides a safe and confidential space to explore and address emotional, psychological, behavioral issues. As your counselor, I am committed to assisting you in addressing the concerns you bring to our sessions. This informed consent form is designed to ensure that you fully understand the counseling process and your rights as a client.</p>
                <p>The regular sessions will last approximately 45-60 minutes with the frequency being according to the needs of the client.</p>
                <p>You have the right to terminate counseling services at any time. If, in my professional judgment, I believe that I am unable to provide the necessary assistance, or if you request services outside my scope of practice, I may terminate the counseling relationship and provide referrals as appropriate.</p>

                <h2 class="bg-gray-100 px-2 py-1 text-xs font-bold uppercase text-gray-700">Cancellation and Scheduling</h2>
                <p>Our sessions will be held on a regular basis, as discussed and agreed upon. If you need to cancel or reschedule a session, please provide at least 24 hours&rsquo; notice.</p>

                <h2 class="bg-gray-100 px-2 py-1 text-xs font-bold uppercase text-gray-700">Confidentiality</h2>
                <p>Counseling sessions are confidential. This means that the content of our sessions is kept private. However, there are legal and ethical exceptions to confidentiality, which include:</p>
                <ol class="list-decimal space-y-2 pl-6">
                    <li><strong>Harm to Self or Others:</strong> If you pose a danger to yourself or others, I may need to take necessary steps to ensure safety.</li>
                    <li><strong>Court Orders:</strong> In certain legal situations, I may be required to disclose information as mandated by a court order.</li>
                    <li><strong>Client&rsquo;s Request:</strong> If you request that I share specific information with another individual or entity, I will do so only with your written consent.</li>
                    <li><strong>Consultation:</strong> For professional development and supervision purposes, I may discuss our sessions with my supervisor. In these instances, your identity will remain protected.</li>
                </ol>

                <h2 class="bg-gray-100 px-2 py-1 text-xs font-bold uppercase text-gray-700">Recording, Observation and Training Purposes</h2>
                <p>By signing this form, you have given permission to the counsellor to record the counselling session in the form of audio or video. You also grant permission to the counsellor&rsquo;s supervisor and participants of the counselling training program to perform observations on the recorded counselling session conducted for the purpose of supervision and education.</p>
                <p>You fully understand and allow the counselling session recorded to be shared by the counsellor to his or her supervisor and the participants of the counselling training program for the purpose of counselling training and education.</p>
                <p>The recordings are to be kept strictly private and confidential, and your identity will remain protected.</p>

                <h2 class="bg-gray-100 px-2 py-1 text-xs font-bold uppercase text-gray-700">Acknowledgement</h2>
                <p>I acknowledge that I have read and understand <strong>ALL</strong> the above information and I am fully aware of my rights. I am also aware of limits to confidentiality. If I have any questions or concerns about any of this information, I agree to discuss these concerns with the counsellor.</p>
            </div>

            <form wire:submit="submit" class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-6">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                        Sign &amp; Submit
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
