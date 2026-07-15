@extends('layouts.app')

@section('title', 'AI Tools')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="AI Features" title="AI plant assistance, diagnosis and planning" description="Front-end modules for the OpenAI, Gemini, Vision, speech-to-text, and text-to-speech integrations described in the plan." />

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                    <label class="block text-sm font-medium text-slate-700">Ask your assistant</label>
                    <div class="mt-2 flex items-center gap-2">
                        <input id="aiQuery" class="flex-1 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm" placeholder="Ask about plants, diseases, or planning...">
                        <label class="inline-flex items-center cursor-pointer">
                            <input id="aiImage" type="file" accept="image/*" class="hidden" multiple>
                            <button id="aiImageBtn" type="button" class="rounded-md border border-slate-300 px-3 py-2 text-sm">+</button>
                        </label>
                        <button id="aiAsk" class="rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Ask</button>
                    </div>

                    <div class="mt-4 text-sm text-slate-600">
                        <p class="font-semibold">What the assistant can do</p>
                        <ul class="mt-2 list-disc pl-5 space-y-1">
                            <li>Disease detection from images</li>
                            <li>AI garden planner and layout suggestions</li>
                            <li>Image creation: pots, stands and decor mockups</li>
                            <li>Product and care recommendations</li>
                        </ul>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <p class="text-sm font-semibold text-slate-900">Assistant results</p>
                    <div id="aiResults" class="mt-4 text-sm text-slate-600">Ask a question or upload images to get started.</div>

                    <div class="mt-4 flex items-center gap-3">
                        <label class="text-sm text-slate-700">Read aloud:</label>
                        <select id="ttsLang" class="rounded-md border p-2 text-sm">
                            <option value="en-IN">English (India)</option>
                            <option value="hi-IN">हिन्दी (Hindi)</option>
                            <option value="ta-IN">தமிழ் (Tamil)</option>
                            <option value="te-IN">తెలుగు (Telugu)</option>
                            <option value="bn-IN">বাংলা (Bengali)</option>
                            <option value="kn-IN">ಕನ್ನಡ (Kannada)</option>
                            <option value="mr-IN">मराठी (Marathi)</option>
                        </select>
                        <button id="ttsSpeak" class="rounded-md bg-emerald-900 px-3 py-2 text-sm font-semibold text-white">Speak</button>
                        <button id="ttsStop" type="button" id="ttsStop" class="rounded-md border px-3 py-2 text-sm">Stop</button>
                    </div>
                </div>
            </div>
            <script>
                (function(){
                    const btn = document.getElementById('aiAsk');
                    const input = document.getElementById('aiQuery');
                    const results = document.getElementById('aiResults');
                    const imgBtn = document.getElementById('aiImageBtn');
                    const imgInput = document.getElementById('aiImage');

                    imgBtn.addEventListener('click', () => imgInput.click());
                    btn.addEventListener('click', async () => {
                        const q = input.value.trim();
                        if(!q && (!imgInput.files || imgInput.files.length === 0)){
                            results.innerHTML = '<p class="text-sm text-red-500">Please enter a question or upload images.</p>';
                            return;
                        }

                        results.innerHTML = '<p class="text-sm text-slate-500">Processing…</p>';

                        const fd = new FormData();
                        fd.append('question', q);
                        for(let i=0;i<imgInput.files.length;i++) fd.append('images[]', imgInput.files[i]);

                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        try{
                            const res = await fetch("/ai/ask", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token
                                },
                                body: fd
                            });

                            const text = await res.text();
                            let json;
                            try{
                                json = JSON.parse(text);
                            } catch(e){
                                // If server returned HTML or plain text, show it instead of attempting to parse JSON
                                if(!res.ok){
                                    results.innerHTML = `<pre class="text-sm text-red-600">${escapeHtml(text)}</pre>`;
                                } else {
                                    results.innerHTML = `<div class="whitespace-pre-wrap">${escapeHtml(text)}</div>`;
                                }
                                return;
                            }

                            if(!res.ok){
                                if (json?.requires_login) {
                                    results.innerHTML = `
                                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                                            <p class="font-semibold">${escapeHtml(json.message || 'Please log in or sign up to continue using AI.')}</p>
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <a href="/customer/login" class="rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Login</a>
                                                <a href="/customer/register" class="rounded-md border border-emerald-900 px-4 py-2 text-sm font-semibold text-emerald-900">Create account</a>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    results.innerHTML = `<pre class="text-sm text-red-600">${escapeHtml(json.error || json.details || 'AI error')}</pre>`;
                                }
                                return;
                            }

                            results.innerHTML = `<div class="whitespace-pre-wrap" id="aiResultText">${escapeHtml(json.result)}</div>`;
                        } catch (err) {
                            results.innerHTML = `<pre class="text-sm text-red-600">${escapeHtml(err.message)}</pre>`;
                        }
                    });

                    // TTS handlers
                    const ttsSpeak = document.getElementById('ttsSpeak');
                    const ttsStop = document.getElementById('ttsStop');
                    const ttsLang = document.getElementById('ttsLang');
                    let currentUtterance = null;

                    function populateVoices(){
                        return new Promise(resolve => {
                            let voices = speechSynthesis.getVoices();
                            if(voices.length) return resolve(voices);
                            speechSynthesis.onvoiceschanged = () => {
                                voices = speechSynthesis.getVoices();
                                resolve(voices);
                            };
                            // fallback
                            setTimeout(()=> resolve(speechSynthesis.getVoices()), 500);
                        });
                    }

                    ttsSpeak.addEventListener('click', async () => {
                        if(!('speechSynthesis' in window)){
                            alert('Text-to-speech not supported in this browser.');
                            return;
                        }
                        const textEl = document.getElementById('aiResultText') || results;
                        const text = (textEl.textContent || textEl.innerText || '').trim();
                        if(!text) return alert('No assistant text to speak.');

                        const lang = ttsLang.value || 'en-IN';
                        const voices = await populateVoices();
                        let voice = voices.find(v => v.lang && v.lang.toLowerCase().startsWith(lang.toLowerCase()));
                        if(!voice){
                            // prefer voices that contain 'India' or 'Indian'
                            voice = voices.find(v => /india|indian/i.test(v.name));
                        }

                        const u = new SpeechSynthesisUtterance(text);
                        u.lang = lang;
                        if(voice) u.voice = voice;
                        u.rate = 1;
                        currentUtterance = u;
                        speechSynthesis.speak(u);
                    });

                    ttsStop.addEventListener('click', () => {
                        if('speechSynthesis' in window){
                            speechSynthesis.cancel();
                            currentUtterance = null;
                        }
                    });

                })();

                function escapeHtml(unsafe){
                    return unsafe
                        .replaceAll('&','&amp;')
                        .replaceAll('<','&lt;')
                        .replaceAll('>','&gt;')
                        .replaceAll('"','&quot;')
                        .replaceAll("'",'&#039;');
                }
            </script>
        </div>
    </section>
@endsection
