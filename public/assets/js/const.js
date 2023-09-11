const ABI_TOKEN = [{
    "inputs": [], "payable": false, "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "owner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "spender", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "value", "type": "uint256"
    }], "name": "Approval", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "sender", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount0", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount1", "type": "uint256"
    }, {
        "indexed": true, "internalType": "address", "name": "to", "type": "address"
    }], "name": "Burn", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "sender", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount0", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount1", "type": "uint256"
    }], "name": "Mint", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "sender", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount0In", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount1In", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount0Out", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "amount1Out", "type": "uint256"
    }, {
        "indexed": true, "internalType": "address", "name": "to", "type": "address"
    }], "name": "Swap", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "uint112", "name": "reserve0", "type": "uint112"
    }, {
        "indexed": false, "internalType": "uint112", "name": "reserve1", "type": "uint112"
    }], "name": "Sync", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "from", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "to", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "value", "type": "uint256"
    }], "name": "Transfer", "type": "event"
}, {
    "constant": true, "inputs": [], "name": "DOMAIN_SEPARATOR", "outputs": [{
        "internalType": "bytes32", "name": "", "type": "bytes32"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "MINIMUM_LIQUIDITY", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "PERMIT_TYPEHASH", "outputs": [{
        "internalType": "bytes32", "name": "", "type": "bytes32"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }, {
        "internalType": "address", "name": "", "type": "address"
    }], "name": "allowance", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "spender", "type": "address"
    }, {
        "internalType": "uint256", "name": "value", "type": "uint256"
    }], "name": "approve", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "balanceOf", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "to", "type": "address"
    }], "name": "burn", "outputs": [{
        "internalType": "uint256", "name": "amount0", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "amount1", "type": "uint256"
    }], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "decimals", "outputs": [{
        "internalType": "uint8", "name": "", "type": "uint8"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "factory", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "getReserves", "outputs": [{
        "internalType": "uint112", "name": "_reserve0", "type": "uint112"
    }, {
        "internalType": "uint112", "name": "_reserve1", "type": "uint112"
    }, {
        "internalType": "uint32", "name": "_blockTimestampLast", "type": "uint32"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "_token0", "type": "address"
    }, {
        "internalType": "address", "name": "_token1", "type": "address"
    }], "name": "initialize", "outputs": [], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "kLast", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "to", "type": "address"
    }], "name": "mint", "outputs": [{
        "internalType": "uint256", "name": "liquidity", "type": "uint256"
    }], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "name", "outputs": [{
        "internalType": "string", "name": "", "type": "string"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "nonces", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "owner", "type": "address"
    }, {
        "internalType": "address", "name": "spender", "type": "address"
    }, {
        "internalType": "uint256", "name": "value", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "deadline", "type": "uint256"
    }, {
        "internalType": "uint8", "name": "v", "type": "uint8"
    }, {
        "internalType": "bytes32", "name": "r", "type": "bytes32"
    }, {
        "internalType": "bytes32", "name": "s", "type": "bytes32"
    }], "name": "permit", "outputs": [], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "price0CumulativeLast", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "price1CumulativeLast", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "to", "type": "address"
    }], "name": "skim", "outputs": [], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "uint256", "name": "amount0Out", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "amount1Out", "type": "uint256"
    }, {
        "internalType": "address", "name": "to", "type": "address"
    }, {
        "internalType": "bytes", "name": "data", "type": "bytes"
    }], "name": "swap", "outputs": [], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "symbol", "outputs": [{
        "internalType": "string", "name": "", "type": "string"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [], "name": "sync", "outputs": [], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "token0", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "token1", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": true, "inputs": [], "name": "totalSupply", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "payable": false, "stateMutability": "view", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "to", "type": "address"
    }, {
        "internalType": "uint256", "name": "value", "type": "uint256"
    }], "name": "transfer", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "payable": false, "stateMutability": "nonpayable", "type": "function"
}, {
    "constant": false, "inputs": [{
        "internalType": "address", "name": "from", "type": "address"
    }, {
        "internalType": "address", "name": "to", "type": "address"
    }, {
        "internalType": "uint256", "name": "value", "type": "uint256"
    }], "name": "transferFrom", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "payable": false, "stateMutability": "nonpayable", "type": "function"
}];
const ABI_PRESALE = [{
    "inputs": [{
        "internalType": "address", "name": "_presaleGenerator", "type": "address"
    }], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "uint256", "name": "baseFeeAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "tokenFeeAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "refererTokenFeeAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseLiquidity", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "tokenLiquidity", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "remainingBaseTokenBalance", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "remainingSaleTokenBalance", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "zeroRoundTokenBurn", "type": "uint256"
    }], "name": "AddLiquidity", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseTokenAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "saleTokenAmount", "type": "uint256"
    }], "name": "BuyToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseTokenAmount", "type": "uint256"
    }], "name": "UserWithdrawBaseToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "saleTokenAmount", "type": "uint256"
    }], "name": "UserWithdrawSaleToken", "type": "event"
}, {
    "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "BUYERS", "outputs": [{
        "internalType": "uint256", "name": "baseDeposited", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenBought", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "PRESALE_LOCK_FORWARDER", "outputs": [{
        "internalType": "contract IPresaleLockForwarder", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "PRESALE_SETTING", "outputs": [{
        "internalType": "contract IPresaleSetting", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "WrapToken", "outputs": [{
        "internalType": "contract IWrapToken", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "addLiquidity", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }], "name": "buyToken", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address[]", "name": "_users", "type": "address[]"
    }, {
        "internalType": "bool", "name": "_add", "type": "bool"
    }], "name": "editWhitelist", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "forceFailByAdmin", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "forceFailIfPairExists", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_address", "type": "address"
    }], "name": "getBuyerInfo", "outputs": [{
        "internalType": "uint256", "name": "baseDeposited", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenBought", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getContractVersion", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getFeeInfo", "outputs": [{
        "internalType": "uint256", "name": "baseFeePercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "refererFeePercent", "type": "uint256"
    }, {
        "internalType": "address", "name": "baseFeeAddress", "type": "address"
    }, {
        "internalType": "address", "name": "tokenFeeAddress", "type": "address"
    }, {
        "internalType": "address", "name": "refererFeeAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getGeneralInfo", "outputs": [{
        "internalType": "uint256", "name": "contractVersion", "type": "uint256"
    }, {
        "internalType": "address", "name": "presaleGenerator", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getListBuyerLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getListBuyerLengthAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPresaleAddressInfo", "outputs": [{
        "internalType": "address", "name": "presaleOwner", "type": "address"
    }, {
        "internalType": "address", "name": "saleToken", "type": "address"
    }, {
        "internalType": "address", "name": "baseToken", "type": "address"
    }, {
        "internalType": "address", "name": "wrapTokenAddress", "type": "address"
    }, {
        "internalType": "address", "name": "dexLockerAddress", "type": "address"
    }, {
        "internalType": "address", "name": "dexFactoryAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPresaleGenerator", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPresaleMainInfo", "outputs": [{
        "internalType": "uint256", "name": "tokenPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "limitPerBuyer", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "hardCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "softCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "liquidityPercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "listingPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "endTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "lockPeriod", "type": "uint256"
    }, {
        "internalType": "bool", "name": "presaleInMainToken", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPresaleRound", "outputs": [{
        "internalType": "int8", "name": "", "type": "int8"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPresaleStatus", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getRoundInfo", "outputs": [{
        "internalType": "bool", "name": "activeZeroRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "activeFirstRound", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getStatusInfo", "outputs": [{
        "internalType": "bool", "name": "whitelistOnly", "type": "bool"
    }, {
        "internalType": "bool", "name": "lpGenerationComplete", "type": "bool"
    }, {
        "internalType": "bool", "name": "forceFailed", "type": "bool"
    }, {
        "internalType": "uint256", "name": "totalBaseCollected", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenSold", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenWithdrawn", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalBaseWithdrawn", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "firstRoundLength", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numBuyers", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "successAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "liquidityAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "currentStatus", "type": "uint256"
    }, {
        "internalType": "int8", "name": "currentRound", "type": "int8"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }], "name": "getUserWhitelistStatus", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistFlag", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getWhitelistedUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistedUsersLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWrapTokenAddress", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundInfo", "outputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "tokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxBaseTokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxSlot", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "registeredSlot", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getZeroRoundUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundUserLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "ownerWithdrawSaleToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "registerZeroRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }], "name": "retrieveBalance", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }], "name": "retrieveToken", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "contract IERC20", "name": "_baseToken", "type": "address"
    }, {
        "internalType": "contract IERC20", "name": "_presaleToken", "type": "address"
    }, {
        "internalType": "uint256", "name": "_baseFeePercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_tokenFeePercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_refererFeePercent", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "_baseFeeAddress", "type": "address"
    }, {
        "internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"
    }, {
        "internalType": "address payable", "name": "_refererAddress", "type": "address"
    }], "name": "setFeeInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_presaleOwner", "type": "address"
    }, {
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_tokenPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_limitPerBuyer", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_hardCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_softCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_liquidityPercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_listingPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_lockPeriod", "type": "uint256"
    }], "name": "setMainInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_activeZeroRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "_activeFirstRound", "type": "bool"
    }], "name": "setRoundInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_flag", "type": "bool"
    }], "name": "setWhitelistFlag", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_limitPerBuyer", "type": "uint256"
    }], "name": "updateLimitPerBuyer", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }], "name": "updateTime", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawSaleToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_PRESALE_GENERATOR = [{
    "inputs": [], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "presaleOwner", "type": "address"
    }, {
        "indexed": false, "internalType": "address", "name": "presaleAddress", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "creationFee", "type": "uint256"
    }], "name": "CreatePresale", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "inputs": [], "name": "PRESALE_FACTORY", "outputs": [{
        "internalType": "contract IPresaleFactory", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "PRESALE_SETTING", "outputs": [{
        "internalType": "contract IPresaleSetting", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_presaleOwner", "type": "address"
    }, {
        "internalType": "contract IERC20", "name": "_presaleToken", "type": "address"
    }, {
        "internalType": "contract IERC20", "name": "_baseToken", "type": "address"
    }, {
        "internalType": "bool[3]", "name": "_activeInfo", "type": "bool[3]"
    }, {
        "internalType": "uint256[10]", "name": "unitParams", "type": "uint256[10]"
    }, {
        "internalType": "uint256[]", "name": "_vestingPeriod", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "_vestingPercent", "type": "uint256[]"
    }], "name": "createPresale", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_PRESALE_SETTING = [{"inputs": [], "stateMutability": "nonpayable", "type": "constructor"}, {"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {
    "inputs": [], "name": "SETTING", "outputs": [{"internalType": "uint256", "name": "BASE_FEE_PERCENT", "type": "uint256"}, {"internalType": "uint256", "name": "TOKEN_FEE_PERCENT", "type": "uint256"}, {"internalType": "address payable", "name": "BASE_FEE_ADDRESS", "type": "address"}, {"internalType": "address payable", "name": "TOKEN_FEE_ADDRESS", "type": "address"}, {"internalType": "uint256", "name": "CREATION_FEE", "type": "uint256"}, {"internalType": "uint256", "name": "FIRST_ROUND_LENGTH", "type": "uint256"}, {"internalType": "uint256", "name": "MAX_PRESALE_LENGTH", "type": "uint256"}, {"internalType": "uint256", "name": "MIN_LIQUIDITY_PERCENT", "type": "uint256"}, {"internalType": "address payable", "name": "ADMIN_ADDRESS", "type": "address"}, {"internalType": "uint256", "name": "MIN_LOCK_PERIOD", "type": "uint256"}, {"internalType": "uint256", "name": "MAX_SUCCESS_TO_LIQUIDITY", "type": "uint256"}, {"internalType": "address", "name": "WRAP_TOKEN_ADDRESS", "type": "address"}, {
        "internalType": "address", "name": "DEX_LOCKER_ADDRESS", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "", "type": "address"}], "name": "WHITELIST_TOKEN_BALANCE", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "ZERO_ROUND", "outputs": [{"internalType": "address", "name": "TOKEN_ADDRESS", "type": "address"}, {"internalType": "uint256", "name": "TOKEN_AMOUNT", "type": "uint256"}, {"internalType": "uint256", "name": "PERCENT", "type": "uint256"}, {"internalType": "uint256", "name": "FINISH_BEFORE_FIRST_ROUND", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_baseToken", "type": "address"}], "name": "baseTokenIsValid", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getAdminAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getBaseFeeAddress", "outputs": [{"internalType": "address payable", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getBaseFeePercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "getBaseTokenAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getCreationFee", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getDexLockerAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getFeeAddresses", "outputs": [{"internalType": "address", "name": "baseFeeAddress", "type": "address"}, {"internalType": "address", "name": "tokenFeeAddress", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getFees", "outputs": [{"internalType": "uint256", "name": "baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "creationFee", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getFinishBeforeFirstRound", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getFirstRoundLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getListBaseTokenLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getMaxPresaleLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getMaxSuccessToLiquidity", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getMinLiquidityPercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getMinLockPeriod", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getSettingAddress", "outputs": [{"internalType": "address", "name": "baseFeeAddress", "type": "address"}, {"internalType": "address", "name": "tokenFeeAddress", "type": "address"}, {"internalType": "address", "name": "adminAddress", "type": "address"}, {"internalType": "address", "name": "wrapTokenAddress", "type": "address"}, {"internalType": "address", "name": "dexLockerAddress", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getSettingInfo", "outputs": [{"internalType": "uint256", "name": "baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "creationFee", "type": "uint256"}, {"internalType": "uint256", "name": "firstRoundLength", "type": "uint256"}, {"internalType": "uint256", "name": "maxPresaleLength", "type": "uint256"}, {"internalType": "uint256", "name": "minLiquidityPercent", "type": "uint256"}, {"internalType": "uint256", "name": "minLockPeriod", "type": "uint256"}, {"internalType": "uint256", "name": "maxSuccessToLiquidity", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getTokenFeeAddress", "outputs": [{"internalType": "address payable", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getTokenFeePercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "getWhitelistTokenAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}, {"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getWrapTokenAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getZeroRound", "outputs": [{"internalType": "address", "name": "tokenAddress", "type": "address"}, {"internalType": "uint256", "name": "tokenAmount", "type": "uint256"}, {"internalType": "uint256", "name": "percent", "type": "uint256"}, {"internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getZeroRoundPercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getZeroRoundTokenAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getZeroRoundTokenAmount", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address payable", "name": "_adminAddress", "type": "address"}], "name": "setAdminAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address payable", "name": "_baseFeeAddress", "type": "address"}], "name": "setBaseFeeAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "_baseFeePercent", "type": "uint256"}], "name": "setBaseFeePercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_creationFee", "type": "uint256"}], "name": "setCreationFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_dexLockerAddress", "type": "address"}], "name": "setDexLockerAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address payable", "name": "_baseFeeAddress", "type": "address"}, {"internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"}], "name": "setFeeAddresses", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "_baseFeePercent", "type": "uint256"}, {
        "internalType": "uint256", "name": "_tokenFeePercent", "type": "uint256"
    }, {"internalType": "uint256", "name": "_creationFee", "type": "uint256"}], "name": "setFees", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"}], "name": "setFinishBeforeFirstRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_firstRoundLength", "type": "uint256"}], "name": "setFirstRoundLength", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_maxLength", "type": "uint256"}], "name": "setMaxPresaleLength", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_time", "type": "uint256"}], "name": "setMaxSuccessToLiquidity", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_minLiquidityPercent", "type": "uint256"}], "name": "setMinLiquidityPercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{
        "internalType": "uint256", "name": "_time", "type": "uint256"
    }], "name": "setMinLockPeriod", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "baseFeeAddress", "type": "address"}, {"internalType": "address", "name": "tokenFeeAddress", "type": "address"}, {"internalType": "address", "name": "adminAddress", "type": "address"}, {"internalType": "address", "name": "wrapTokenAddress", "type": "address"}, {"internalType": "address", "name": "dexLockerAddress", "type": "address"}], "name": "setSettingAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "creationFee", "type": "uint256"}, {"internalType": "uint256", "name": "firstRoundLength", "type": "uint256"}, {"internalType": "uint256", "name": "maxPresaleLength", "type": "uint256"}, {"internalType": "uint256", "name": "minLiquidityPercent", "type": "uint256"}, {
        "internalType": "uint256", "name": "minLockPeriod", "type": "uint256"
    }, {"internalType": "uint256", "name": "maxSuccessToLiquidity", "type": "uint256"}], "name": "setSettingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"}], "name": "setTokenFeeAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_tokenFeePercent", "type": "uint256"}], "name": "setTokenFeePercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_wrapTokenAddress", "type": "address"}], "name": "setWrapTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "tokenAddress", "type": "address"}, {"internalType": "uint256", "name": "tokenAmount", "type": "uint256"}, {"internalType": "uint256", "name": "percent", "type": "uint256"}, {"internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"}], "name": "setZeroRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "_percent", "type": "uint256"}], "name": "setZeroRoundPercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_tokenAddress", "type": "address"}], "name": "setZeroRoundTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_tokenAmount", "type": "uint256"}], "name": "setZeroRoundTokenAmount", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "_baseToken", "type": "address"}, {"internalType": "bool", "name": "_allow", "type": "bool"}], "name": "updateBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "_token", "type": "address"}, {"internalType": "uint256", "name": "_holdAmount", "type": "uint256"}, {"internalType": "bool", "name": "_allow", "type": "bool"}], "name": "updateWhitelistToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_user", "type": "address"}], "name": "userHoldSufficientFirstRoundToken", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "whitelistTokenLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}];
const ABI_PRESALE_FACTORY = [{
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "presaleContract", "type": "address"
    }], "name": "PresaleRegistered", "type": "event"
}, {
    "inputs": [{
        "internalType": "address", "name": "_address", "type": "address"
    }, {
        "internalType": "bool", "name": "_allow", "type": "bool"
    }], "name": "adminAllowPresaleGenerator", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "presaleAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "presaleGeneratorAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_presaleGeneratorAddress", "type": "address"
    }], "name": "presaleGeneratorIsValid", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "presaleGeneratorsLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_presaleAddress", "type": "address"
    }], "name": "presaleIsRegistered", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "presalesLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_presaleAddress", "type": "address"
    }], "name": "registerPresale", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_MINT_TOKEN_FACTORY = [{
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "indexed": false, "internalType": "address", "name": "tokenOwner", "type": "address"
    }], "name": "TokenRegistered", "type": "event"
}, {
    "inputs": [{
        "internalType": "address", "name": "_address", "type": "address"
    }, {
        "internalType": "bool", "name": "_allow", "type": "bool"
    }], "name": "adminAllowTokenGenerator", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenOwner", "type": "address"
    }, {
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getTokenByOwnerAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenOwner", "type": "address"
    }], "name": "getTokensLengthByOwner", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenAddress", "type": "address"
    }], "name": "isToken", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenOwner", "type": "address"
    }, {
        "internalType": "address", "name": "_tokenAddress", "type": "address"
    }], "name": "registerToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "tokenAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "tokenGeneratorAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenGenerator", "type": "address"
    }], "name": "tokenGeneratorIsAllowed", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "tokenGeneratorsLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "tokensLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_MINT_TOKEN_SETTING = [{
    "inputs": [], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "inputs": [], "name": "SETTING", "outputs": [{
        "internalType": "uint256", "name": "CREATION_FEE", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "TOTAL_SUPPLY_FEE", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "TOKEN_FEE_ADDRESS", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getCreationFee", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSettingInfo", "outputs": [{
        "internalType": "uint256", "name": "creationFee", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalSupplyFee", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "tokenFeeAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getTokenFeeAddress", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getTotalSupplyFee", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_creationFee", "type": "uint256"
    }], "name": "setCreationFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_creationFee", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_totalSupplyFee", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"
    }], "name": "setSettingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"
    }], "name": "setTokenFeeAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_totalSupplyFee", "type": "uint256"
    }], "name": "setTotalSupplyFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_MINT_TOKEN_GENERATOR = [{
    "inputs": [{
        "internalType": "address", "name": "_tokenFactory", "type": "address"
    }, {
        "internalType": "address", "name": "_tokenSetting", "type": "address"
    }], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "userAddress", "type": "address"
    }, {
        "indexed": false, "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "creationFee", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "totalSupplyFee", "type": "uint256"
    }], "name": "CreateToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "inputs": [], "name": "TOKEN_FACTORY", "outputs": [{
        "internalType": "contract ITokenFactory", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "TOKEN_SETTING", "outputs": [{
        "internalType": "contract ITokenSetting", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "string", "name": "name", "type": "string"
    }, {
        "internalType": "string", "name": "symbol", "type": "string"
    }, {
        "internalType": "uint8", "name": "decimals", "type": "uint8"
    }, {
        "internalType": "uint256", "name": "totalSupply", "type": "uint256"
    }], "name": "createToken", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_AIRDROP_SETTING = [{"inputs": [], "stateMutability": "nonpayable", "type": "constructor"}, {"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {"inputs": [], "name": "SETTING", "outputs": [{"internalType": "uint256", "name": "FEE_AMOUNT", "type": "uint256"}, {"internalType": "address payable", "name": "FEE_ADDRESS", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getFeeAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getFeeAmount", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getSettingInfo", "outputs": [{"internalType": "uint256", "name": "feeAmount", "type": "uint256"}, {"internalType": "address payable", "name": "feeAddress", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address payable", "name": "_feeAddress", "type": "address"}], "name": "setFeeAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_feeAmount", "type": "uint256"}], "name": "setFeeAmount", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_feeAmount", "type": "uint256"}, {"internalType": "address payable", "name": "_feeAddress", "type": "address"}], "name": "setSettingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_AIRDROP_CONTRACT = [{
    "inputs": [{
        "internalType": "address", "name": "_airdropSettingAddress", "type": "address"
    }], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "inputs": [], "name": "AIRDROP_SETTING", "outputs": [{
        "internalType": "contract IAirdropSetting", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address[]", "name": "listAddress", "type": "address[]"
    }, {
        "internalType": "uint256[]", "name": "listAmount", "type": "uint256[]"
    }], "name": "airdropMain", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "address[]", "name": "listAddress", "type": "address[]"
    }, {
        "internalType": "uint256[]", "name": "listAmount", "type": "uint256[]"
    }], "name": "airdropToken", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_LOCK_SETTING = [{
    "inputs": [], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "uint256", "name": "oldValue", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "newValue", "type": "uint256"
    }], "name": "UpdateDiscountPercent", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "oldAddress", "type": "address"
    }, {
        "indexed": false, "internalType": "bool", "name": "status", "type": "bool"
    }], "name": "UpdateWhitelistAddress", "type": "event"
}, {
    "inputs": [], "name": "SETTING", "outputs": [{
        "internalType": "uint256", "name": "BASE_FEE", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "TOKEN_FEE", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "ADDRESS_FEE", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "WHITELIST_FEE_MAP", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAddressFee", "outputs": [{
        "internalType": "address payable", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getBaseFee", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getDiscountPercent", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getTokenFee", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getWhitelistAddressAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistAddressLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }], "name": "getWhitelistAddressStatus", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getWhitelistFeeTokenAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }, {
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistFeeTokenLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_baseFee", "type": "uint256"
    }], "name": "setBaseFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_discountPercent", "type": "uint256"
    }], "name": "setDiscountPercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_baseFee", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_tokenFee", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "_addressFee", "type": "address"
    }], "name": "setFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_addressFee", "type": "address"
    }], "name": "setFeeAddresses", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_tokenFee", "type": "uint256"
    }], "name": "setTokenFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_token", "type": "address"
    }, {
        "internalType": "uint256", "name": "_holdAmount", "type": "uint256"
    }, {
        "internalType": "bool", "name": "_allow", "type": "bool"
    }], "name": "setWhitelistFeeToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }, {
        "internalType": "bool", "name": "_status", "type": "bool"
    }], "name": "updateFeeWhitelist", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }], "name": "userHoldSufficientWhitelistToken", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}];
const ABI_SALE_SETTING = [{"inputs": [], "stateMutability": "nonpayable", "type": "constructor"}, {"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {
    "inputs": [], "name": "SETTING", "outputs": [{"internalType": "uint256", "name": "BASE_FEE_PERCENT", "type": "uint256"}, {"internalType": "uint256", "name": "TOKEN_FEE_PERCENT", "type": "uint256"}, {"internalType": "address payable", "name": "BASE_FEE_ADDRESS", "type": "address"}, {"internalType": "address payable", "name": "TOKEN_FEE_ADDRESS", "type": "address"}, {"internalType": "uint256", "name": "CREATION_FEE", "type": "uint256"}, {"internalType": "uint256", "name": "FIRST_ROUND_LENGTH", "type": "uint256"}, {"internalType": "uint256", "name": "MAX_SALE_LENGTH", "type": "uint256"}, {"internalType": "address payable", "name": "ADMIN_ADDRESS", "type": "address"}, {"internalType": "uint256", "name": "MAX_SUCCESS_TO_CLAIM", "type": "uint256"}, {"internalType": "address", "name": "WRAP_TOKEN_ADDRESS", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "", "type": "address"}], "name": "WHITELIST_TOKEN_BALANCE", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "ZERO_ROUND", "outputs": [{"internalType": "address", "name": "TOKEN_ADDRESS", "type": "address"}, {"internalType": "uint256", "name": "TOKEN_AMOUNT", "type": "uint256"}, {"internalType": "uint256", "name": "PERCENT", "type": "uint256"}, {"internalType": "uint256", "name": "FINISH_BEFORE_FIRST_ROUND", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_baseToken", "type": "address"}], "name": "baseTokenIsValid", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getAdminAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getBaseFeeAddress", "outputs": [{"internalType": "address payable", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getBaseFeePercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "getBaseTokenAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getCreationFee", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getFeeAddresses", "outputs": [{"internalType": "address", "name": "baseFeeAddress", "type": "address"}, {"internalType": "address", "name": "tokenFeeAddress", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getFees", "outputs": [{"internalType": "uint256", "name": "baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "creationFee", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getFinishBeforeFirstRound", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getFirstRoundLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getListBaseTokenLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getMaxSaleLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getMaxSuccessToClaim", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getSettingAddress", "outputs": [{"internalType": "address", "name": "baseFeeAddress", "type": "address"}, {"internalType": "address", "name": "tokenFeeAddress", "type": "address"}, {"internalType": "address", "name": "adminAddress", "type": "address"}, {"internalType": "address", "name": "wrapTokenAddress", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getSettingInfo", "outputs": [{"internalType": "uint256", "name": "baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "creationFee", "type": "uint256"}, {"internalType": "uint256", "name": "firstRoundLength", "type": "uint256"}, {"internalType": "uint256", "name": "maxSaleLength", "type": "uint256"}, {"internalType": "uint256", "name": "maxSuccessToClaim", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getTokenFeeAddress", "outputs": [{"internalType": "address payable", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getTokenFeePercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "getWhitelistTokenAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}, {"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "getWrapTokenAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getZeroRound", "outputs": [{"internalType": "address", "name": "tokenAddress", "type": "address"}, {"internalType": "uint256", "name": "tokenAmount", "type": "uint256"}, {"internalType": "uint256", "name": "percent", "type": "uint256"}, {"internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getZeroRoundPercent", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "getZeroRoundTokenAddress", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "getZeroRoundTokenAmount", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address payable", "name": "_adminAddress", "type": "address"}], "name": "setAdminAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address payable", "name": "_baseFeeAddress", "type": "address"}], "name": "setBaseFeeAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_baseFeePercent", "type": "uint256"}], "name": "setBaseFeePercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "_creationFee", "type": "uint256"}], "name": "setCreationFee", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "address payable", "name": "_baseFeeAddress", "type": "address"}, {"internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"}], "name": "setFeeAddresses", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "_tokenFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "_creationFee", "type": "uint256"}], "name": "setFees", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"}], "name": "setFinishBeforeFirstRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "_firstRoundLength", "type": "uint256"}], "name": "setFirstRoundLength", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "_maxLength", "type": "uint256"}], "name": "setMaxSaleLength", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_time", "type": "uint256"}], "name": "setMaxSuccessToClaim", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "baseFeeAddress", "type": "address"}, {"internalType": "address", "name": "tokenFeeAddress", "type": "address"}, {"internalType": "address", "name": "adminAddress", "type": "address"}, {"internalType": "address", "name": "wrapTokenAddress", "type": "address"}], "name": "setSettingAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "baseFeePercent", "type": "uint256"}, {"internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"}, {
        "internalType": "uint256", "name": "creationFee", "type": "uint256"
    }, {"internalType": "uint256", "name": "firstRoundLength", "type": "uint256"}, {"internalType": "uint256", "name": "maxSaleLength", "type": "uint256"}, {"internalType": "uint256", "name": "maxSuccessToClaim", "type": "uint256"}], "name": "setSettingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"}], "name": "setTokenFeeAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_tokenFeePercent", "type": "uint256"}], "name": "setTokenFeePercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_wrapTokenAddress", "type": "address"}], "name": "setWrapTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "tokenAddress", "type": "address"}, {"internalType": "uint256", "name": "tokenAmount", "type": "uint256"}, {"internalType": "uint256", "name": "percent", "type": "uint256"}, {"internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"}], "name": "setZeroRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "_percent", "type": "uint256"}], "name": "setZeroRoundPercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_tokenAddress", "type": "address"}], "name": "setZeroRoundTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_tokenAmount", "type": "uint256"}], "name": "setZeroRoundTokenAmount", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "_baseToken", "type": "address"}, {"internalType": "bool", "name": "_allow", "type": "bool"}], "name": "updateBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "_token", "type": "address"}, {"internalType": "uint256", "name": "_holdAmount", "type": "uint256"}, {"internalType": "bool", "name": "_allow", "type": "bool"}], "name": "updateWhitelistToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_user", "type": "address"}], "name": "userHoldSufficientFirstRoundToken", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "whitelistTokenLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}];
const ABI_SALE_GENERATOR = [{"inputs": [], "stateMutability": "nonpayable", "type": "constructor"}, {"anonymous": false, "inputs": [{"indexed": false, "internalType": "address", "name": "saleOwner", "type": "address"}, {"indexed": false, "internalType": "address", "name": "saleAddress", "type": "address"}, {"indexed": false, "internalType": "uint256", "name": "creationFee", "type": "uint256"}], "name": "CreateSale", "type": "event"}, {"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {"inputs": [], "name": "SALE_FACTORY", "outputs": [{"internalType": "contract ISaleFactory", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "SALE_SETTING", "outputs": [{"internalType": "contract ISaleSetting", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "address payable", "name": "_saleOwner", "type": "address"}, {"internalType": "contract IERC20", "name": "_saleToken", "type": "address"}, {"internalType": "contract IERC20", "name": "_baseToken", "type": "address"}, {"internalType": "bool[3]", "name": "_activeInfo", "type": "bool[3]"}, {"internalType": "uint256[7]", "name": "unitParams", "type": "uint256[7]"}, {"internalType": "uint256[]", "name": "_vestingPeriod", "type": "uint256[]"}, {"internalType": "uint256[]", "name": "_vestingPercent", "type": "uint256[]"}], "name": "createSale", "outputs": [], "stateMutability": "payable", "type": "function"}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_SALE_FACTORY = [{"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {"anonymous": false, "inputs": [{"indexed": false, "internalType": "address", "name": "saleContract", "type": "address"}], "name": "SaleRegistered", "type": "event"}, {"inputs": [{"internalType": "address", "name": "_address", "type": "address"}, {"internalType": "bool", "name": "_allow", "type": "bool"}], "name": "adminAllowSaleGenerator", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "_saleAddress", "type": "address"}], "name": "registerSale", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "saleAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "saleGeneratorAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_saleGeneratorAddress", "type": "address"}], "name": "saleGeneratorIsValid", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "saleGeneratorsLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "_saleAddress", "type": "address"}], "name": "saleIsRegistered", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "salesLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}];
const ABI_SALE = [{
    "inputs": [{
        "internalType": "address", "name": "_saleGenerator", "type": "address"
    }], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "uint256", "name": "baseFeeAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "tokenFeeAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "remainingBaseTokenBalance", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "remainingSaleTokenBalance", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "zeroRoundTokenBurn", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "refundAmount", "type": "uint256"
    }], "name": "ActiveClaim", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseTokenAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "saleTokenAmount", "type": "uint256"
    }], "name": "BuyToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseTokenAmount", "type": "uint256"
    }], "name": "UserWithdrawBaseToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "saleTokenAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "numberClaimed", "type": "uint256"
    }], "name": "UserWithdrawSaleToken", "type": "event"
}, {
    "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "BUYERS", "outputs": [{
        "internalType": "uint256", "name": "baseDeposited", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenBought", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenClaimed", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numberClaimed", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "SALE_SETTING", "outputs": [{
        "internalType": "contract ISaleSetting", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "WrapToken", "outputs": [{
        "internalType": "contract IWrapToken", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "activeClaim", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }], "name": "buyToken", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address[]", "name": "_users", "type": "address[]"
    }, {
        "internalType": "bool", "name": "_add", "type": "bool"
    }], "name": "editWhitelist", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "forceFailByAdmin", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_address", "type": "address"
    }], "name": "getBuyerInfo", "outputs": [{
        "internalType": "uint256", "name": "baseDeposited", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenBought", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenClaimed", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numberClaimed", "type": "uint256"
    }, {
        "internalType": "uint256[]", "name": "historyTimeClaimed", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "historyAmountClaimed", "type": "uint256[]"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getFeeInfo", "outputs": [{
        "internalType": "uint256", "name": "baseFeePercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenFeePercent", "type": "uint256"
    }, {
        "internalType": "address", "name": "baseFeeAddress", "type": "address"
    }, {
        "internalType": "address", "name": "tokenFeeAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getGeneralInfo", "outputs": [{
        "internalType": "uint256", "name": "contractVersion", "type": "uint256"
    }, {
        "internalType": "string", "name": "contractType", "type": "string"
    }, {
        "internalType": "address", "name": "saleGenerator", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getListBuyerLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getListBuyerLengthAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getRoundInfo", "outputs": [{
        "internalType": "bool", "name": "activeZeroRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "activeFirstRound", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSaleAddressInfo", "outputs": [{
        "internalType": "address", "name": "saleOwner", "type": "address"
    }, {
        "internalType": "address", "name": "saleToken", "type": "address"
    }, {
        "internalType": "address", "name": "baseToken", "type": "address"
    }, {
        "internalType": "address", "name": "wrapTokenAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSaleMainInfo", "outputs": [{
        "internalType": "uint256", "name": "tokenPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "limitPerBuyer", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "hardCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "softCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "endTime", "type": "uint256"
    }, {
        "internalType": "bool", "name": "saleInMainToken", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSaleRound", "outputs": [{
        "internalType": "int8", "name": "", "type": "int8"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSaleStatus", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getStatusInfo", "outputs": [{
        "internalType": "bool", "name": "whitelistOnly", "type": "bool"
    }, {
        "internalType": "bool", "name": "isActiveClaim", "type": "bool"
    }, {
        "internalType": "bool", "name": "forceFailed", "type": "bool"
    }, {
        "internalType": "uint256", "name": "totalBaseCollected", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenSold", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenWithdrawn", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalBaseWithdrawn", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "firstRoundLength", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numBuyers", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "successAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "activeClaimAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "currentStatus", "type": "uint256"
    }, {
        "internalType": "int8", "name": "currentRound", "type": "int8"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }], "name": "getUserWhitelistStatus", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getVestingInfo", "outputs": [{
        "internalType": "bool", "name": "activeVesting", "type": "bool"
    }, {
        "internalType": "uint256[]", "name": "vestingPeriod", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "vestingPercent", "type": "uint256[]"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistFlag", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getWhitelistedUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistedUsersLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundInfo", "outputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "tokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxBaseTokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxSlot", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "registeredSlot", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getZeroRoundUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundUserLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "ownerWithdrawSaleToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "registerZeroRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }], "name": "retrieveBalance", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }], "name": "retrieveToken", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "contract IERC20", "name": "_baseToken", "type": "address"
    }, {
        "internalType": "contract IERC20", "name": "_saleToken", "type": "address"
    }, {
        "internalType": "uint256", "name": "_baseFeePercent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_tokenFeePercent", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "_baseFeeAddress", "type": "address"
    }, {
        "internalType": "address payable", "name": "_tokenFeeAddress", "type": "address"
    }], "name": "setFeeInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_saleOwner", "type": "address"
    }, {
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_tokenPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_limitPerBuyer", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_hardCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_softCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }], "name": "setMainInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_activeZeroRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "_activeFirstRound", "type": "bool"
    }], "name": "setRoundInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_activeVesting", "type": "bool"
    }, {
        "internalType": "uint256[]", "name": "_vestingPeriod", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "_vestingPercent", "type": "uint256[]"
    }], "name": "setVestingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_flag", "type": "bool"
    }], "name": "setWhitelistFlag", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_limitPerBuyer", "type": "uint256"
    }], "name": "updateLimitPerBuyer", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }], "name": "updateTime", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawSaleToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_POOL_SETTING = [{
    "inputs": [], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"
    }, {
        "indexed": true, "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "OwnershipTransferred", "type": "event"
}, {
    "inputs": [], "name": "AUCTION_ROUND", "outputs": [{
        "internalType": "address", "name": "TOKEN_ADDRESS", "type": "address"
    }, {
        "internalType": "uint256", "name": "FINISH_BEFORE_FIRST_ROUND", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "SETTING", "outputs": [{
        "internalType": "uint256", "name": "FIRST_ROUND_LENGTH", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "MAX_POOL_LENGTH", "type": "uint256"
    }, {
        "internalType": "address payable", "name": "ADMIN_ADDRESS", "type": "address"
    }, {
        "internalType": "address", "name": "WRAP_TOKEN_ADDRESS", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "WHITELIST_TOKEN_BALANCE", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "ZERO_ROUND", "outputs": [{
        "internalType": "address", "name": "TOKEN_ADDRESS", "type": "address"
    }, {
        "internalType": "uint256", "name": "TOKEN_AMOUNT", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "PERCENT", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "FINISH_BEFORE_FIRST_ROUND", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_baseToken", "type": "address"
    }], "name": "baseTokenIsValid", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_creatorAddress", "type": "address"
    }], "name": "creatorAddressIsValid", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAdminAddress", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAuctionRound", "outputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAuctionRoundFinishBeforeFirstRound", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAuctionRoundTokenAddress", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getBaseTokenAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getCreatorAddressAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getFirstRoundLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getListBaseTokenLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getListCreatorAddressLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getMaxPoolLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSettingAddress", "outputs": [{
        "internalType": "address", "name": "adminAddress", "type": "address"
    }, {
        "internalType": "address", "name": "wrapTokenAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getSettingInfo", "outputs": [{
        "internalType": "uint256", "name": "firstRoundLength", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxPoolLength", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getWhitelistTokenAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }, {
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWrapTokenAddress", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRound", "outputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "tokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundFinishBeforeFirstRound", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundPercent", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundTokenAddress", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundTokenAmount", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "owner", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_adminAddress", "type": "address"
    }], "name": "setAdminAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }], "name": "setAuctionRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }], "name": "setAuctionRoundFinishBeforeFirstRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenAddress", "type": "address"
    }], "name": "setAuctionRoundTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_firstRoundLength", "type": "uint256"
    }], "name": "setFirstRoundLength", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_maxLength", "type": "uint256"
    }], "name": "setMaxPoolLength", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "adminAddress", "type": "address"
    }, {
        "internalType": "address", "name": "wrapTokenAddress", "type": "address"
    }], "name": "setSettingAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "firstRoundLength", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxPoolLength", "type": "uint256"
    }], "name": "setSettingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_wrapTokenAddress", "type": "address"
    }], "name": "setWrapTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "tokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }], "name": "setZeroRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }], "name": "setZeroRoundFinishBeforeFirstRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_percent", "type": "uint256"
    }], "name": "setZeroRoundPercent", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_tokenAddress", "type": "address"
    }], "name": "setZeroRoundTokenAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_tokenAmount", "type": "uint256"
    }], "name": "setZeroRoundTokenAmount", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "newOwner", "type": "address"
    }], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_baseToken", "type": "address"
    }, {
        "internalType": "bool", "name": "_allow", "type": "bool"
    }], "name": "updateBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_creatorAddress", "type": "address"
    }, {
        "internalType": "bool", "name": "_allow", "type": "bool"
    }], "name": "updateCreatorAddress", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_token", "type": "address"
    }, {
        "internalType": "uint256", "name": "_holdAmount", "type": "uint256"
    }, {
        "internalType": "bool", "name": "_allow", "type": "bool"
    }], "name": "updateWhitelistToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }], "name": "userHoldSufficientFirstRoundToken", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "whitelistTokenLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}];
const ABI_POOL_FACTORY = [{"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {"anonymous": false, "inputs": [{"indexed": false, "internalType": "address", "name": "poolContract", "type": "address"}], "name": "PoolRegistered", "type": "event"}, {"inputs": [{"internalType": "address", "name": "_address", "type": "address"}, {"internalType": "bool", "name": "_allow", "type": "bool"}], "name": "adminAllowPoolGenerator", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "poolAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "uint256", "name": "_index", "type": "uint256"}], "name": "poolGeneratorAtIndex", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_poolGeneratorAddress", "type": "address"}], "name": "poolGeneratorIsValid", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "poolGeneratorsLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"}, {"inputs": [{"internalType": "address", "name": "_poolAddress", "type": "address"}], "name": "poolIsRegistered", "outputs": [{"internalType": "bool", "name": "", "type": "bool"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "poolsLength", "outputs": [{"internalType": "uint256", "name": "", "type": "uint256"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "address", "name": "_poolAddress", "type": "address"}], "name": "registerPool", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {"inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}];
const ABI_POOL_GENERATOR = [{"inputs": [], "stateMutability": "nonpayable", "type": "constructor"}, {"anonymous": false, "inputs": [{"indexed": false, "internalType": "address", "name": "poolOwner", "type": "address"}, {"indexed": false, "internalType": "address", "name": "poolAddress", "type": "address"}], "name": "CreatePool", "type": "event"}, {"anonymous": false, "inputs": [{"indexed": true, "internalType": "address", "name": "previousOwner", "type": "address"}, {"indexed": true, "internalType": "address", "name": "newOwner", "type": "address"}], "name": "OwnershipTransferred", "type": "event"}, {"inputs": [], "name": "POOL_FACTORY", "outputs": [{"internalType": "contract IPoolFactory", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {
    "inputs": [], "name": "POOL_SETTING", "outputs": [{"internalType": "contract IPoolSetting", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"
}, {"inputs": [{"internalType": "address payable", "name": "_poolOwner", "type": "address"}, {"internalType": "contract IERC20", "name": "_poolToken", "type": "address"}, {"internalType": "contract IERC20", "name": "_baseToken", "type": "address"}, {"internalType": "bool[4]", "name": "_activeInfo", "type": "bool[4]"}, {"internalType": "uint256[8]", "name": "unitParams", "type": "uint256[8]"}, {"internalType": "uint256[]", "name": "_vestingPeriod", "type": "uint256[]"}, {"internalType": "uint256[]", "name": "_vestingPercent", "type": "uint256[]"}], "name": "createPool", "outputs": [], "stateMutability": "payable", "type": "function"}, {"inputs": [], "name": "owner", "outputs": [{"internalType": "address", "name": "", "type": "address"}], "stateMutability": "view", "type": "function"}, {"inputs": [], "name": "renounceOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"}, {
    "inputs": [{"internalType": "address", "name": "newOwner", "type": "address"}], "name": "transferOwnership", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_POOL = [{
    "inputs": [{
        "internalType": "address", "name": "_poolGenerator", "type": "address"
    }], "stateMutability": "nonpayable", "type": "constructor"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "uint256", "name": "zeroRoundTokenBurn", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "auctionRoundTokenBurn", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "notSoldToken", "type": "uint256"
    }], "name": "ActiveClaim", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseTokenAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "poolTokenAmount", "type": "uint256"
    }], "name": "BuyToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "tokenAmount", "type": "uint256"
    }], "name": "UserWithdrawAuctionToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "baseTokenAmount", "type": "uint256"
    }], "name": "UserWithdrawBaseToken", "type": "event"
}, {
    "anonymous": false, "inputs": [{
        "indexed": false, "internalType": "address", "name": "user", "type": "address"
    }, {
        "indexed": false, "internalType": "uint256", "name": "poolTokenAmount", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "indexed": false, "internalType": "uint256", "name": "numberClaimed", "type": "uint256"
    }], "name": "UserWithdrawPoolToken", "type": "event"
}, {
    "inputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "name": "BUYERS", "outputs": [{
        "internalType": "uint256", "name": "baseDeposited", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenBought", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenClaimed", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numberClaimed", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "POOL_SETTING", "outputs": [{
        "internalType": "contract IPoolSetting", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "WrapToken", "outputs": [{
        "internalType": "contract IWrapToken", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "activeClaim", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }], "name": "buyToken", "outputs": [], "stateMutability": "payable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address[]", "name": "_users", "type": "address[]"
    }, {
        "internalType": "bool", "name": "_add", "type": "bool"
    }], "name": "editWhitelist", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "forceFailByAdmin", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "getAuctionAmountToBurn", "outputs": [{
        "internalType": "uint256", "name": "burnAmount", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAuctionRoundInfo", "outputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "endTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "registeredSlot", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "burnedTokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "refundTokenAmount", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getAuctionUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_address", "type": "address"
    }], "name": "getAuctionUserInfo", "outputs": [{
        "internalType": "uint256", "name": "auctionAmount", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getAuctionUserLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_address", "type": "address"
    }], "name": "getBuyerInfo", "outputs": [{
        "internalType": "uint256", "name": "baseDeposited", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenBought", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "tokenClaimed", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numberClaimed", "type": "uint256"
    }, {
        "internalType": "uint256[]", "name": "historyTimeClaimed", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "historyAmountClaimed", "type": "uint256[]"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getGeneralInfo", "outputs": [{
        "internalType": "uint256", "name": "contractVersion", "type": "uint256"
    }, {
        "internalType": "string", "name": "contractType", "type": "string"
    }, {
        "internalType": "address", "name": "poolGenerator", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getListBuyerLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getListBuyerLengthAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPoolAddressInfo", "outputs": [{
        "internalType": "address", "name": "poolOwner", "type": "address"
    }, {
        "internalType": "address", "name": "poolToken", "type": "address"
    }, {
        "internalType": "address", "name": "baseToken", "type": "address"
    }, {
        "internalType": "address", "name": "wrapTokenAddress", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPoolMainInfo", "outputs": [{
        "internalType": "uint256", "name": "tokenPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "limitPerBuyer", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "hardCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "softCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "endTime", "type": "uint256"
    }, {
        "internalType": "bool", "name": "poolInMainToken", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPoolRound", "outputs": [{
        "internalType": "int8", "name": "", "type": "int8"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getPoolStatus", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getRoundInfo", "outputs": [{
        "internalType": "bool", "name": "activeZeroRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "activeFirstRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "activeAuctionRound", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getStatusInfo", "outputs": [{
        "internalType": "bool", "name": "whitelistOnly", "type": "bool"
    }, {
        "internalType": "bool", "name": "isActiveClaim", "type": "bool"
    }, {
        "internalType": "bool", "name": "forceFailed", "type": "bool"
    }, {
        "internalType": "uint256", "name": "totalBaseCollected", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenSold", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalTokenWithdrawn", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "totalBaseWithdrawn", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "firstRoundLength", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "numBuyers", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "successAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "activeClaimAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "currentStatus", "type": "uint256"
    }, {
        "internalType": "int8", "name": "currentRound", "type": "int8"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "_user", "type": "address"
    }], "name": "getUserWhitelistStatus", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getVestingInfo", "outputs": [{
        "internalType": "bool", "name": "activeVesting", "type": "bool"
    }, {
        "internalType": "uint256[]", "name": "vestingPeriod", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "vestingPercent", "type": "uint256[]"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistFlag", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getWhitelistedUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getWhitelistedUsersLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundInfo", "outputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "tokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "percent", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishBeforeFirstRound", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "finishAt", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxBaseTokenAmount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "maxSlot", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "registeredSlot", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_index", "type": "uint256"
    }], "name": "getZeroRoundUserAtIndex", "outputs": [{
        "internalType": "address", "name": "", "type": "address"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "getZeroRoundUserLength", "outputs": [{
        "internalType": "uint256", "name": "", "type": "uint256"
    }], "stateMutability": "view", "type": "function"
}, {
    "inputs": [], "name": "ownerWithdrawBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "ownerWithdrawPoolToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }], "name": "registerAuctionRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "registerZeroRound", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }], "name": "retrieveBalance", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address", "name": "tokenAddress", "type": "address"
    }, {
        "internalType": "uint256", "name": "amount", "type": "uint256"
    }], "name": "retrieveToken", "outputs": [{
        "internalType": "bool", "name": "", "type": "bool"
    }], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }], "name": "setAuctionRoundInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "address payable", "name": "_poolOwner", "type": "address"
    }, {
        "internalType": "uint256", "name": "_amount", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_tokenPrice", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_limitPerBuyer", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_hardCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_softCap", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }], "name": "setMainInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_activeZeroRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "_activeFirstRound", "type": "bool"
    }, {
        "internalType": "bool", "name": "_activeAuctionRound", "type": "bool"
    }], "name": "setRoundInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "contract IERC20", "name": "_baseToken", "type": "address"
    }, {
        "internalType": "contract IERC20", "name": "_poolToken", "type": "address"
    }], "name": "setTokenInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_activeVesting", "type": "bool"
    }, {
        "internalType": "uint256[]", "name": "_vestingPeriod", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "_vestingPercent", "type": "uint256[]"
    }], "name": "setVestingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "bool", "name": "_flag", "type": "bool"
    }], "name": "setWhitelistFlag", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_limitPerBuyer", "type": "uint256"
    }], "name": "updateLimitPerBuyer", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256", "name": "_startTime", "type": "uint256"
    }, {
        "internalType": "uint256", "name": "_endTime", "type": "uint256"
    }], "name": "updateTime", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [{
        "internalType": "uint256[]", "name": "_vestingPeriod", "type": "uint256[]"
    }, {
        "internalType": "uint256[]", "name": "_vestingPercent", "type": "uint256[]"
    }], "name": "updateVestingInfo", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawAuctionToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawBaseToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}, {
    "inputs": [], "name": "userWithdrawPoolToken", "outputs": [], "stateMutability": "nonpayable", "type": "function"
}];
const ABI_LOTTERY = [
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "_paymentTokenAddress",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_randomGeneratorAddress",
                "type": "address"
            }
        ],
        "stateMutability": "nonpayable",
        "type": "constructor"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "lotteryId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "firstTicketIdNextLottery",
                "type": "uint256"
            }
        ],
        "name": "LotteryClose",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "lotteryId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "injectedAmount",
                "type": "uint256"
            }
        ],
        "name": "LotteryInjection",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "lotteryId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "finalNumber",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "countWinningTickets",
                "type": "uint256"
            }
        ],
        "name": "LotteryNumberDrawn",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "lotteryId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "startTime",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "endTime",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "priceTicket",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "firstTicketId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "injectedAmount",
                "type": "uint256"
            }
        ],
        "name": "LotteryOpen",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": false,
                "internalType": "address",
                "name": "operator",
                "type": "address"
            },
            {
                "indexed": false,
                "internalType": "address",
                "name": "treasury",
                "type": "address"
            },
            {
                "indexed": false,
                "internalType": "address",
                "name": "injector",
                "type": "address"
            }
        ],
        "name": "NewOperatorAndTreasuryAndInjectorAddresses",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "address",
                "name": "randomGenerator",
                "type": "address"
            }
        ],
        "name": "NewRandomGenerator",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "address",
                "name": "previousOwner",
                "type": "address"
            },
            {
                "indexed": true,
                "internalType": "address",
                "name": "newOwner",
                "type": "address"
            }
        ],
        "name": "OwnershipTransferred",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "address",
                "name": "claimer",
                "type": "address"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "amount",
                "type": "uint256"
            },
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "lotteryId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "numberTickets",
                "type": "uint256"
            }
        ],
        "name": "TicketsClaim",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "address",
                "name": "buyer",
                "type": "address"
            },
            {
                "indexed": true,
                "internalType": "uint256",
                "name": "lotteryId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "numberTickets",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256[]",
                "name": "listTicketId",
                "type": "uint256[]"
            }
        ],
        "name": "TicketsPurchase",
        "type": "event"
    },
    {
        "inputs": [],
        "name": "MAX_LENGTH_LOTTERY",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "MAX_TREASURY_FEE",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "MIN_DISCOUNT_DIVISOR",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "MIN_LENGTH_LOTTERY",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            },
            {
                "internalType": "uint32[]",
                "name": "_ticketNumbers",
                "type": "uint32[]"
            }
        ],
        "name": "buyTickets",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            },
            {
                "internalType": "bool",
                "name": "_autoInjection",
                "type": "bool"
            }
        ],
        "name": "calculateReward",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_discountDivisor",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_priceTicket",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_numberTickets",
                "type": "uint256"
            }
        ],
        "name": "calculateTotalPriceForBulkTickets",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "pure",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "_randomGeneratorAddress",
                "type": "address"
            }
        ],
        "name": "changeRandomGenerator",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            },
            {
                "internalType": "uint256[]",
                "name": "_ticketIds",
                "type": "uint256[]"
            },
            {
                "internalType": "uint32[]",
                "name": "_brackets",
                "type": "uint32[]"
            }
        ],
        "name": "claimTickets",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            }
        ],
        "name": "closeLottery",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "currentLotteryId",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "currentTicketId",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            }
        ],
        "name": "getRewardInfo",
        "outputs": [
            {
                "internalType": "uint256[6]",
                "name": "rewardsBreakdown",
                "type": "uint256[6]"
            },
            {
                "internalType": "uint256[6]",
                "name": "tokenPerTicketInBracket",
                "type": "uint256[6]"
            },
            {
                "internalType": "uint256[6]",
                "name": "countWinnersPerBracket",
                "type": "uint256[6]"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_amount",
                "type": "uint256"
            }
        ],
        "name": "injectFunds",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "injectorAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "maxNumberTicketsPerBuyOrClaim",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "maxPriceTicket",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "minPriceTicket",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "operatorAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "owner",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "paymentToken",
        "outputs": [
            {
                "internalType": "contract IERC20",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "pendingInjectionNextLottery",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "randomGenerator",
        "outputs": [
            {
                "internalType": "contract IRandomNumberGenerator",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "renounceOwnership",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "tokenAddress",
                "type": "address"
            },
            {
                "internalType": "uint256",
                "name": "amount",
                "type": "uint256"
            },
            {
                "internalType": "address",
                "name": "userAddress",
                "type": "address"
            }
        ],
        "name": "retrieveToken",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "_operatorAddress",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_treasuryAddress",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_injectorAddress",
                "type": "address"
            }
        ],
        "name": "setAddress",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_maxNumberTicketsPerBuy",
                "type": "uint256"
            }
        ],
        "name": "setMaxNumberTicketsPerBuy",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_minPriceTicket",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_maxPriceTicket",
                "type": "uint256"
            }
        ],
        "name": "setMinAndMaxTicketPrice",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_endTime",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_priceTicket",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_discountDivisor",
                "type": "uint256"
            },
            {
                "internalType": "uint256[6]",
                "name": "_rewardsBreakdown",
                "type": "uint256[6]"
            },
            {
                "internalType": "uint256",
                "name": "_treasuryFee",
                "type": "uint256"
            }
        ],
        "name": "startLottery",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "newOwner",
                "type": "address"
            }
        ],
        "name": "transferOwnership",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "treasuryAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "viewCurrentLotteryId",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            }
        ],
        "name": "viewLottery",
        "outputs": [
            {
                "internalType": "uint32",
                "name": "status",
                "type": "uint32"
            },
            {
                "internalType": "uint256",
                "name": "startTime",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "endTime",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "priceTicket",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "discountDivisor",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "treasuryFee",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "firstTicketId",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "firstTicketIdNextLottery",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "amountCollected",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "amountRoundCollected",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "numberBuyer",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "finalNumber",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256[]",
                "name": "_ticketIds",
                "type": "uint256[]"
            }
        ],
        "name": "viewNumbersAndStatusesForTicketIds",
        "outputs": [
            {
                "internalType": "uint32[]",
                "name": "",
                "type": "uint32[]"
            },
            {
                "internalType": "bool[]",
                "name": "",
                "type": "bool[]"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_ticketId",
                "type": "uint256"
            },
            {
                "internalType": "uint32",
                "name": "_bracket",
                "type": "uint32"
            }
        ],
        "name": "viewRewardsForTicketId",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "_user",
                "type": "address"
            },
            {
                "internalType": "uint256",
                "name": "_lotteryId",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_cursor",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "_size",
                "type": "uint256"
            }
        ],
        "name": "viewUserInfoForLotteryId",
        "outputs": [
            {
                "internalType": "uint256[]",
                "name": "",
                "type": "uint256[]"
            },
            {
                "internalType": "uint32[]",
                "name": "",
                "type": "uint32[]"
            },
            {
                "internalType": "bool[]",
                "name": "",
                "type": "bool[]"
            },
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    }
];
const ABI_STAKING = [
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "swapTokenAmount",
                "type": "uint256"
            }
        ],
        "name": "buy",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "_stakingToken",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_swapToken",
                "type": "address"
            }
        ],
        "stateMutability": "nonpayable",
        "type": "constructor"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": false,
                "internalType": "address",
                "name": "userAddress",
                "type": "address"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "swapTokenAmount",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "swapTokenBalance",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "stakingTokenBalance",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "stakingId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "address",
                "name": "paymentToken",
                "type": "address"
            }
        ],
        "name": "BuyToken",
        "type": "event"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": true,
                "internalType": "address",
                "name": "previousOwner",
                "type": "address"
            },
            {
                "indexed": true,
                "internalType": "address",
                "name": "newOwner",
                "type": "address"
            }
        ],
        "name": "OwnershipTransferred",
        "type": "event"
    },
    {
        "inputs": [],
        "name": "renounceOwnership",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "_operatorAddress",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_dexPairAddress",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_feeStakingAddress",
                "type": "address"
            },
            {
                "internalType": "address",
                "name": "_feeSwapAddress",
                "type": "address"
            }
        ],
        "name": "setAddress",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "tokenAmount",
                "type": "uint256"
            }
        ],
        "name": "staking",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "anonymous": false,
        "inputs": [
            {
                "indexed": false,
                "internalType": "address",
                "name": "userAddress",
                "type": "address"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "tokenAmount",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "uint256",
                "name": "stakingId",
                "type": "uint256"
            },
            {
                "indexed": false,
                "internalType": "address",
                "name": "paymentToken",
                "type": "address"
            }
        ],
        "name": "StakingToken",
        "type": "event"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "amount",
                "type": "uint256"
            },
            {
                "internalType": "address",
                "name": "userAddress",
                "type": "address"
            }
        ],
        "name": "takeBalance",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "tokenAddress",
                "type": "address"
            },
            {
                "internalType": "uint256",
                "name": "amount",
                "type": "uint256"
            },
            {
                "internalType": "address",
                "name": "userAddress",
                "type": "address"
            }
        ],
        "name": "takeToken",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "toggleRunning",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "newOwner",
                "type": "address"
            }
        ],
        "name": "transferOwnership",
        "outputs": [],
        "stateMutability": "nonpayable",
        "type": "function"
    },
    {
        "stateMutability": "payable",
        "type": "fallback"
    },
    {
        "stateMutability": "payable",
        "type": "receive"
    },
    {
        "inputs": [],
        "name": "_isRunning",
        "outputs": [
            {
                "internalType": "bool",
                "name": "",
                "type": "bool"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "name": "allStakingId",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "dexPairAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "feeStakingAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "feeSwapAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "name": "listStakingItem",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "id",
                "type": "uint256"
            },
            {
                "internalType": "address",
                "name": "userAddress",
                "type": "address"
            },
            {
                "internalType": "uint256",
                "name": "amount",
                "type": "uint256"
            },
            {
                "internalType": "uint256",
                "name": "stakingTime",
                "type": "uint256"
            },
            {
                "internalType": "address",
                "name": "paymentToken",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "operatorAddress",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "owner",
        "outputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            }
        ],
        "name": "stakingAmountByAddress",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "stakingId",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [
            {
                "internalType": "address",
                "name": "",
                "type": "address"
            },
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "name": "stakingIdByAddress",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "stakingToken",
        "outputs": [
            {
                "internalType": "contract IERC20",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "swapToken",
        "outputs": [
            {
                "internalType": "contract IERC20",
                "name": "",
                "type": "address"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "TOKEN_DECIMALS",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "totalBoughtAmount",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    },
    {
        "inputs": [],
        "name": "totalDepositedAmount",
        "outputs": [
            {
                "internalType": "uint256",
                "name": "",
                "type": "uint256"
            }
        ],
        "stateMutability": "view",
        "type": "function"
    }
];
let AIRDROP_ADDRESS = "0x1ba3285c88cbeacf58f623eb92b50a84cdb258d2";

const ETH_CHAIN_ID = {
    1: "main", 3: "test", 4: "test", 5: "test", 42: "test",
};
const BSC_CHAIN_ID = {
    56: "main", 97: "test",
};

const POLYGON_CHAIN_ID = {
    137: "main", 80001: "test",
};