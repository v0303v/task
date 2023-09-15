@include('includes/head')

@include('includes/header')

<form method="POST" action="{{ url('handler') }}" class="col-lg-6 offset-lg-3" name="form_registration">
    @csrf
    <div class="row justify-content-center g-3">

        <div class="col-12">
            <label for="first_name" class="form-label">Имя</label>
            <input type="text" name="first_name" id="first_name" value="" placeholder="" class="form-control" required>
        </div>

        <div class="col-12">
            <label for="last_name" class="form-label">Фамилия</label>
            <input type="text" name="last_name" id="last_name" value="" placeholder="" class="form-control" required>
        </div>

        <div class="col-12">
            <label for="age" class="form-label">Возраст</label>
            <input type="number" name="age" id="age" value="" placeholder="" class="form-control" required>
        </div>

        <div class="col-12">
            <label for="gender" class="form-label">Пол</label>
            <select class="form-select" name="gender" id="gender">
                <option name="male" value="Мужчина">мужчина</option>
                <option name="female" value="Женщина">женщина</option>
            </select>
        </div>

        <div class="col-12">
            <label for="phone_number" class="form-label">Телефон</label>
            <input type="text" name="phone_number" id="phone_number" min="0" value="" placeholder=""
                   class="form-control" required>
        </div>

        <div class="col-12">
            <label for="email" class="form-label">Электронная почта</label>
            <input type="email" name="email" id="email" value="" placeholder="" class="form-control" required>
            <span class="error" aria-live="polite"></span>
        </div>

    </div>

    <hr>
    @if($errors->any())
        <div class="notification is-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <button class="btn btn-primary" type="button" id="submitBtn">Continue</button>
</form>

@include('includes/footer')
