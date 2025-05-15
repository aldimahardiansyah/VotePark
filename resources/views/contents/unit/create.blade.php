<x-layout.main>
    <x-slot name="title">Add Unit</x-slot>

    <div class="container">
        <h1>Add Unit</h1>
        <hr>

        <x-alert.success-and-error />

        <form action="{{ route('unit.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="npp" class="form-label">NPP</label>
                        <input type="text" class="form-control" id="npp" name="npp" value="{{ old('npp') }}" required>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="wide" class="form-label">Luas</label>
                        <input type="text" class="form-control" id="wide" name="wide" value="{{ old('wide') }}" required>
                    </div>
                </div>
            </div>
            <hr>

            <div class="mb-3">
                <label for="user_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="{{ old('user_name') }}" required>
            </div>
            <div class="mb-3">
                <label for="user_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="user_email" name="user_email" value="{{ old('user_email') }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <a href="{{ route('unit.index') }}" class="btn btn-secondary mt-3">Back to Units</a>
    </div>
</x-layout.main>
