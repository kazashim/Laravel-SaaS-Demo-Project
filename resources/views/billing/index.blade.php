@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">My Plan</div>

                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-info">{{ session('message') }}</div>
                    @endif

                    @if (is_null($currentPlan))
                        You are now on Free Plan. Please choose plan to upgrade:
                        <br /><br />
                    @elseif ($currentPlan->trial_ends_at)
                        <div class="alert alert-info">Your trial will end on {{ $currentPlan->trial_ends_at->toDateString() }} and your card will be charged.</div>
                        <br /><br />
                    @endif
                        <div class="row">
                            <div class="col">
                                <input type="radio" name="billing_period" value="monthly" checked /> Monthly
                                <input type="radio" name="billing_period" value="yearly" /> Yearly
                            </div>
                        </div>
                        <hr />
                    <div class="row" id="plans_monthly">
                        @foreach ($monthlyPlans as $plan)
                            <div class="col-md-4 text-center">
                                <h3>{{ $plan->name }}</h3>
                                <b>${{ number_format($plan->price / 100, 2) }} / month</b>
                                <hr />
                                @if (!is_null($currentPlan) && $plan->stripe_plan_id == $currentPlan->stripe_plan)
                                    Your current plan.
                                    <br />
                                    @if (!$currentPlan->onGracePeriod())
                                        <a href="{{ route('cancel') }}" class="btn btn-danger" onclick="return confirm('Are you sure?')">Cancel plan</a>
                                    @else
                                        Your subscription will end on {{ $currentPlan->ends_at->toDateString() }}
                                        <br /><br />
                                        <a href="{{ route('resume') }}" class="btn btn-primary">Resume subscription</a>
                                    @endif
                                @else
                                    <a href="{{ route('checkout', $plan->id) }}" class="btn btn-primary">Subscribe to {{ $plan->name }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="row d-none" id="plans_yearly">
                        @foreach ($yearlyPlans as $plan)
                            <div class="col-md-4 text-center">
                                <h3>{{ $plan->name }}</h3>
                                <b>${{ number_format($plan->price / 100, 2) }} / year</b>
                                <hr />
                                @if (!is_null($currentPlan) && $plan->stripe_plan_id == $currentPlan->stripe_plan)
                                    Your current plan.
                                    <br />
                                    @if (!$currentPlan->onGracePeriod())
                                        <a href="{{ route('cancel') }}" class="btn btn-danger" onclick="return confirm('Are you sure?')">Cancel plan</a>
                                    @else
                                        Your subscription will end on {{ $currentPlan->ends_at->toDateString() }}
                                        <br /><br />
                                        <a href="{{ route('resume') }}" class="btn btn-primary">Resume subscription</a>
                                    @endif
                                @else
                                    <a href="{{ route('checkout', $plan->id) }}" class="btn btn-primary">Subscribe to {{ $plan->name }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if (!is_null($currentPlan))
                <br />
            <div class="card">
                <div class="card-header">Payment Methods</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Expires at</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($paymentMethods as $paymentMethod)
                            <tr>
                                <td>{{ $paymentMethod->card->brand }}</td>
                                <td>{{ $paymentMethod->card->exp_month }} / {{ $paymentMethod->card->exp_year }}</td>
                                <td>
                                    @if ($defaultPaymentMethod->id == $paymentMethod->id)
                                        default
                                    @else
                                        <a href="{{ route('payment-methods.markDefault', $paymentMethod->id) }}">Mark as Default</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">Add Payment Method</a>
                </div>
            </div>
            @endif

            <br />
            <div class="card">
                <div class="card-header">Payment History</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at }}</td>
                                <td>${{ number_format($payment->total / 100, 2) }}</td>
                                <td>
                                    <a href="{{ route('invoices.download', $payment->id) }}" class="btn btn-sm btn-primary">Download invoice</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
      $(function() {
        $('input[name=billing_period]').change(function() {
          $('#plans_yearly').addClass('d-none');
          $('#plans_monthly').addClass('d-none');
          let billing_period = $(this).filter(':checked').val();
          $('#plans_' + billing_period).removeClass('d-none');
        });
      });
    </script>
@endsection