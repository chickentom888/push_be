"use strict";

let web3;
const Web3Modal = window.Web3Modal.default;
const WalletConnectProvider = window.WalletConnectProvider.default;
const evmChains = window.evmChains;

// Web3modal instance
let web3Modal

// Chosen wallet provider given by the dialog window
let provider, chainId;
let platform, network;

// Address of the selected account
let selectedAccount;

/**
 * Setup the orchestra
 */
function init() {

    // Check that the web page is run in a secure context, as otherwise MetaMask won't be available
    if (location.protocol !== 'https:') {
        // https://ethereum.stackexchange.com/a/62217/620
        return;
    }

    const providerOptions = {
    	walletconnect: {
    		package: WalletConnectProvider,
    		options: {
    			rpc: {
    				56: "https://bsc-dataseed1.defibit.io/",
    				97: "https://data-seed-prebsc-2-s1.binance.org:8545/"
    			},
    		}
    	},
    };
    // const providerOptions = {};

    web3Modal = new Web3Modal({
        cacheProvider: true,
        providerOptions,
        disableInjectedProvider: false,
    });
}

/**
 * Connect wallet button pressed.
 */
async function onConnect() {

    try {
        provider = await web3Modal.connect();
        web3 = new Web3(provider);
        displayConnectedWallet();
    } catch (e) {
        console.log("Could not get a wallet connection", e);
        return;
    }

    provider.on("accountsChanged", async (accounts) => {
        await fetchAccountData();
        window.location.reload();
    });

    provider.on("chainChanged", async (networkId) => {
        await fetchAccountData();
        window.location.reload();
    });

    await refreshAccountData();
}

/**
 * Kick in the UI action after Web3modal dialog has chosen a provider
 */
async function fetchAccountData() {

    // Get connected chain id from Ethereum node
    chainId = await web3.eth.getChainId();

    // Load chain information over an HTTP API
    const chainData = evmChains.getChain(chainId);

    // Get list of accounts of the connected wallet
    const accounts = await web3.eth.getAccounts();

    // MetaMask does not give you all accounts, only the selected account
    selectedAccount = accounts[0];

    // Go through all accounts and get their ETH balance
    const rowResolvers = accounts.map(async (address) => {
        const balance = await web3.eth.getBalance(address);
        const ethBalance = web3.utils.fromWei(balance, "ether");
        // console.log('balance', balance, ethBalance);
    });

    await Promise.all(rowResolvers);

    setConnectedWallet(selectedAccount, chainData);
    displayConnectedWallet();
}

async function refreshAccountData() {
    await fetchAccountData(provider);
}

/**
 * Disconnect wallet button pressed.
 */
async function onDisconnect() {
    if (provider != null) {
        await web3Modal.clearCachedProvider();
        provider = null;
    }

    selectedAccount = null;
    setConnectedWallet(selectedAccount, null);
    displayConnectedWallet();
}

/**
 * Main entry point.
 */
window.addEventListener('load', async () => {
    $("#global_loading").hide();
    init();
    // await onConnect();
    displayConnectedWallet();
});

function displayConnectedWallet() {
    if (selectedAccount != null && selectedAccount.length) {
        // connected
        $('#wallet_connected').val(selectedAccount);
        $('#platform_connected').val(platform.toUpperCase());
        $('#network_connected').val(network.toUpperCase());
    } else {
        // not connect
        $('#wallet_connected, #platform_connected, #network_connected').val('');
    }
}

function setConnectedWallet(address, chainData) {

    setPlatformAndNetwork(chainId);

    setButtonInteract();

    $.ajax({
        url: '/index/selectedAddress',
        type: "POST",
        data: {
            address: address,
            chain_id: chainData ? chainData.chainId : null,
            chain: chainData ? chainData.chain : null,
            network: network,
            platform: platform,
            network_id: chainData ? chainData.networkId : null
        },
        dataType: 'json',
        success: function (data) {

        },
    });
}

function setPlatformAndNetwork(chainId) {
    if (!chainId) {
        platform = null;
        network = null;
    } else {
        chainId = chainId.toString();
        let listKeyBsc = Object.keys(BSC_CHAIN_ID);
         if (listKeyBsc.indexOf(chainId) >= 0) {
            platform = 'bsc';
            network = BSC_CHAIN_ID[chainId];
        } else {
            platform = 'bsc';
            network = 'main';
        }
    }
}

function setButtonInteract() {
    let selectedPlatformNetwork = $('#setting_platform_network');
    let selectedPlatform = selectedPlatformNetwork.attr('data-platform');
    let selectedNetwork = selectedPlatformNetwork.attr('data-network');

    if (selectedPlatform !== platform || selectedNetwork !== network) {
        $('.btn-interact-sc').hide();
    }
}

async function checkConnect() {
    if (provider == null) {
        init();
        await onConnect();
        return false;
    }
}