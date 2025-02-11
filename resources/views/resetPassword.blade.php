<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Loopple/loopple-public-assets@main/motion-tailwind/motion-tailwind.css" rel="stylesheet">
</head>

<body class="bg-white rounded-lg py-5">
    <div class="container flex flex-col mx-auto bg-white rounded-lg pt-12 my-5">
        <div class="flex justify-center w-full h-full my-auto xl:gap-14 lg:justify-normal md:gap-5 draggable">
            <div class="flex items-center justify-center w-full lg:p-12">
                <div class="flex items-center xl:p-10">
                    <form action="{{ route('password.update') }}" method="POST" class="flex flex-col w-full h-full pb-6 text-center bg-white rounded-3xl">
						@csrf
                        <h3 class="mb-3 text-4xl font-extrabold text-dark-grey-900">Reset Password</h3>
                        <p class="mb-4 text-grey-700">Masukkan email dan password</p>
                        <div class="flex items-center mb-3">
                            <hr class="h-0 border-b border-solid border-grey-500 grow">
                            <p class="mx-4 text-grey-600">Reset</p>
                            <hr class="h-0 border-b border-solid border-grey-500 grow">
                        </div>
                        <input type="hidden" value="{{$token}}" name="token" id="">
                        <label for="email" class="mb-2 text-sm text-start text-grey-900">Email*</label>
                        <input id="email" name="email" type="email" placeholder="Masukkan Email" class="flex items-center w-full px-5 py-4 mr-2 text-sm font-medium outline-none focus:bg-grey-400 mb-7 placeholder:text-grey-700 bg-grey-200 text-dark-grey-900 rounded-2xl" />
                        <label for="password" class="mb-2 text-sm text-start text-grey-900">Password*</label>
                        <input id="password" name="password" type="password" placeholder="Masukkan Password" class="flex items-center w-full px-5 py-4 mb-5 mr-2 text-sm font-medium outline-none focus:bg-grey-400 placeholder:text-grey-700 bg-grey-200 text-dark-grey-900 rounded-2xl"
                        />
                        <label for="password_confirmation" class="mb-2 text-sm text-start text-grey-900">Konfirmasi Password*</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Masukkan Password" class="flex items-center w-full px-5 py-4 mb-5 mr-2 text-sm font-medium outline-none focus:bg-grey-400 placeholder:text-grey-700 bg-grey-200 text-dark-grey-900 rounded-2xl"
                        />
						@if(session('success'))
							<div class="p-4 mb-4 text-sm text-green-800 bg-green-100 rounded-lg" role="alert" style="color: white;font-weight:bold;">
								{{ session('success') }}
							</div>
							{{ session()->forget('success') }}
						@endif

						@if(session('error'))
							<div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert" style="background-color: red;color: white;font-weight:bold;">
								{{ session('error') }}
							</div>
							{{ session()->forget('error') }}
						@endif

                        <button type="submit" class="w-full px-6 py-5 mb-5 text-sm font-bold leading-none text-white transition duration-300 md:w-96 rounded-2xl hover:bg-purple-blue-600 focus:ring-4 focus:ring-purple-blue-100 bg-purple-blue-500">Submit</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<html>