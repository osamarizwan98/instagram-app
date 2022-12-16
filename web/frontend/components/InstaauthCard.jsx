import { useEffect, useState } from "react";
import {
  Card,
  Heading,
  TextContainer,
  DisplayText,
  TextStyle,
} from "@shopify/polaris";
import { Toast } from "@shopify/app-bridge-react";
import { useAppQuery, useAuthenticatedFetch } from "../hooks";
import { useSearchParams } from "react-router-dom";

export function InstaauthCard() {
  const emptyToastProps = { content: null };
  const [isLoading, setIsLoading] = useState(false);
  const [toastProps, setToastProps] = useState(emptyToastProps);
  const [URL, setURL] = useState('');
  const fetch = useAuthenticatedFetch();
    const [searchParams] = useSearchParams();

  useEffect(() => {
    // console.log({searchParams: searchParams.get('code')});
    let code = searchParams.get('code');
    if (code) {
        console.log('code------------', code);
    }
  }, [searchParams])


  
  // const {
  //   data,
  //   refetch: refetchProductCount,
  //   isLoading: isLoadingCount,
  //   isRefetching: isRefetchingCount,
  // } = useAppQuery({
  //   url: "/api/products/count",
  //   reactQueryOptions: {
  //     onSuccess: () => {
  //       setIsLoading(false);
  //     },
  //   },
  // });
  

  const toastMarkup = toastProps.content && (
    <Toast {...toastProps} onDismiss={() => setToastProps(emptyToastProps)} />
  );

 const connectInstagram = async () => {
    setIsLoading(true);
    const response = await fetch("/api/loginInsta");
    if (response.ok) {
      let instaResponse = await response.json();
      console.log(instaResponse);
         window.open(instaResponse.url, "_blank")
        setURL(instaResponse.url)
      setIsLoading(false);
      // await refetchProductCount();
      // setToastProps({ content: "5 products created!" });
    } else {
      setIsLoading(false);
      setToastProps({
        content: "There was an error creating products",
        error: true,
      });
    }
  };


  return (
    <>
      {toastMarkup}
      <Card
        title="Product Counter"
        sectioned
        primaryFooterAction={{
          content: "Connect With Insta",
          onAction: connectInstagram,
          loading: isLoading,
        }}
        
      >
        <TextContainer spacing="loose">
          <p>Login with instagram to show Insta feed at site.</p>
        </TextContainer>
      </Card>

    </>
  );
}
