import 'package:flutter/material.dart';
import 'donate_tab.dart';
import 'my_donations_tab.dart';
import 'mentions_tab.dart';

class DonationScreen extends StatefulWidget {
  const DonationScreen({super.key});

  @override
  State<DonationScreen> createState() => _DonationScreenState();
}

class _DonationScreenState extends State<DonationScreen> {
  final List<String> paymentTypes = [
    'Scan QR',
    'Paypal',
    'Stripe',
    'Plux',
    'Datafast',
    'Other',
  ];

  String? selectedPayment;
  bool showOtherImage = false;

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Donation'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Donate', icon: Icon(Icons.volunteer_activism)),
              Tab(text: 'My donations', icon: Icon(Icons.bar_chart)),
              Tab(text: 'Mentions', icon: Icon(Icons.people)),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Donate Tab
            Padding(
              padding: const EdgeInsets.all(24.0),
              child: DonateTab(
                paymentTypes: paymentTypes,
                selectedPayment: selectedPayment,
                showOtherImage: showOtherImage,
                onPaymentChanged: (value) {
                  if (value == 'Scan QR') {
                    Navigator.pushNamed(context, '/qr-scan');
                    return;
                  }
                  setState(() {
                    selectedPayment = value;
                    showOtherImage = value == 'Other';
                  });
                },
              ),
            ),
            // My Donations Tab
            const Padding(
              padding: EdgeInsets.all(24.0),
              child: MyDonationsTab(),
            ),
            // Mentions Tab
            const Padding(
              padding: EdgeInsets.all(24.0),
              child: MentionsTab(),
            ),
          ],
        ),
      ),
    );
  }
}